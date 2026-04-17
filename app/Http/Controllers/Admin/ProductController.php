<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Family;
use App\Models\Grid;
use App\Models\Product;
use App\Models\Section;
use App\Models\Variation;
use Illuminate\Support\Str;
use App\Models\Presentation;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\MeasurementUnit;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:products_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:products_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:products_delete', ['only' => ['destroy']]);
        $this->middleware('store');
    }

    public function index(Request $request)
    {
        $brands = Brand::where('is_enabled', true)
            ->where('tenant_id', session('store')['tenant_id'])
            ->orderBy('name')->get();

        $families = Family::where('is_enabled', true)->orderBy('name')->get();

        $sections = Section::where('is_enabled', true)
            ->where('store_id', session('store')['id'])
            ->where('type', 'A')
            ->orderBy('name')
            ->get();

        $variation = Variation::where('is_enabled', true)
            ->orderBy('grid_id')
            ->get();

        $data =  Product::with('variation')->info()
            ->when(isset($request->is_enabled), function ($q) use ($request) {
                return $q->where('products.is_enabled', $request->is_enabled);
            })
            ->when(!empty($request->search), function ($q) use ($request) {
                return $q->where('commercial_name', 'LIKE', "%$request->search%");
            })
            ->when(!empty($request->section_id), function ($q) use ($request) {
                return $q->where('section_id', $request->section_id);
            })
            ->when(!empty($request->brand_id), function ($q) use ($request) {
                return $q->where('brand_id', $request->brand_id);
            })
            ->when(!empty($request->type), function ($q) use ($request) {
                return $q->where('products.type', $request->type);
            })
            ->when(!empty($request->sku), function ($q) use ($request) {
                return $q->where('sku', 'LIKE', "%$request->sku%");
            })
            ->where('products.store_id', session('store')['id'])
            ->orderBy('commercial_name')
            ->paginate(10);

        return view('products.index', compact('data', 'brands', 'families', 'sections', 'variation'));
    }

    public function create()
    {
        $reference = Product::getReference();

        $grids = Grid::with('variation')->where('is_enabled', true)->orderBy('grid')->get();

        $products = Product::where('is_enabled', true)
            ->where('store_id', session('store')['id'])
            ->orderBy('commercial_name')
            ->get();

        $ums = MeasurementUnit::where('is_enabled', true)->orderBy('name')->get();

        $paymentMethods = PaymentMethod::where('is_enabled', true)
            ->whereHas('stores', fn ($query) => $query->where('stores.id', session('store')['id']))
            ->orderBy('description')
            ->get();

        $brands = Brand::where('is_enabled', true)
            ->where('tenant_id', session('store')['tenant_id'])
            ->orderBy('name')
            ->get();

        $families = Family::where('is_enabled', true)->orderBy('name')->get();

        $presentations = Presentation::where('is_enabled', true)->orderBy('name')->get();

        $sections = Section::where('is_enabled', true)
            ->where('store_id', session('store')['id'])
            ->orderBy('name')
            ->get();

        $sinteticasSemAnaliticas = Section::where('type', 'S')
        ->whereNotIn('id', function ($query) {
            $query->select('parent_id')
                ->from('sections')
                ->where('type', 'A');
        })
        ->get();

        return view('products.create', compact(
            'products',
            'ums',
            'paymentMethods',
            'brands',
            'families',
            'presentations',
            'sections',
            'grids',
            'reference',
            'sinteticasSemAnaliticas'
        ));
    }

    public function store(Request $request)
    {
        $variantes = boolval($request->is_grid) ?
            Variation::validatorVariation($request->variation0, $request->variation1, $request->variation2, $request->variation3) : '';

        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $inputs = $request->all();
        $inputs['brand_id'] = $request->filled('brand_id') ? $request->brand_id : null;
        $inputs['price'] = moneyToFloat($request->price);
        $inputs['discount'] = moneyToFloat($request->discount);
        $inputs['weight'] = moneyToFloat($request->weight);
        $inputs['cubic_weight'] = moneyToFloat($request->cubic_weight);
        $inputs['length'] = moneyToFloat($request->length);
        $inputs['width'] = moneyToFloat($request->width);
        $inputs['height'] = moneyToFloat($request->height);
        $inputs['promotion_price'] = moneyToFloat($request->promotion_price);
        $inputs['store_id'] = session('store')['id'];
        try {
            DB::beginTransaction();
            if ($request->is_grid) {
                    $inputs['commercial_name'] = $request->commercial_name;

                    $product = Product::create($inputs);

                    if (!empty($request->images)) {
                        foreach ($request->images as $image) {

                            $upload = $image->store('products', 'public');
                            $product->images()->create(['name' => $upload]);
                        }
                    }

                    if (!empty($request->products)) {
                        $product->products()->attach($request->products);
                    }

                    if (!empty($request->paymentMethods)) {
                        $product->paymentMethods()->attach($request->paymentMethods);
                    }

                    if (!empty($request->sections)) {
                        $product->sections()->attach($request->sections);
                    }


                    if ($product->is_grid == '1' && !empty($variantes)) {
                        $sku = $product->reference;
                        
                        foreach ($variantes as $variacoes) {
                            foreach ($variacoes as $variacaoId) {
                                // Verifique se a variação existe no banco de dados
                                $variation = Variation::findOrFail($variacaoId);
                                
                                // Anexe a variação ao produto
                                $product->variation()->attach($variation->id);
                            }
                        }
                        
                        // Atualize o SKU do produto
                        $product->sku = $sku;
                        $product->save();
                    }
            } else {
                $inputs['commercial_name'] = $request->commercial_name;

                $product = Product::create($inputs);

                if (!empty($request->images)) {
                    foreach ($request->images as $image) {

                        $upload = $image->store('products', 'public');

                        $product->images()->create(['name' => $upload]);
                    }
                }

                if (!empty($request->products)) {
                    $product->products()->attach($request->products);
                }

                if (!empty($request->paymentMethods)) {
                    $product->paymentMethods()->attach($request->paymentMethods);
                }

                if (!empty($request->sections)) {
                    $product->sections()->attach($request->sections);
                }
            }
            
            DB::commit();
            
            return redirect()->route('products.index')->withStatus('Registro adicionado com sucesso.');
        } catch (\Throwable $th) {
            DB::rollBack();
            
            return redirect()->route('products.index')
                ->withError('Registro não inserido.' . $th->getMessage());
        }
    }

    public function show($id)
    {
        $item = Product::with('variation')->findOrFail($id);

        return view('products.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Product::with('images', 'products', 'paymentMethods', 'sections')->findOrFail($id);

        $grids = Grid::with('variation')->where('is_enabled', true)->orderBy('grid')->get();

        $products = Product::where('is_enabled', true)
            ->where('store_id', session('store')['id'])
            ->orderBy('commercial_name')
            ->get();

        $ums = MeasurementUnit::where('is_enabled', true)->orderBy('name')->get();

        $paymentMethods = PaymentMethod::where('is_enabled', true)
            ->whereHas('stores', fn ($query) => $query->where('stores.id', session('store')['id']))
            ->orderBy('description')
            ->get();

        $brands = Brand::where('is_enabled', true)
            ->where('tenant_id', session('store')['tenant_id'])
            ->orderBy('name')
            ->get();

        $families = Family::where('is_enabled', true)->orderBy('name')->get();

        $presentations = Presentation::where('is_enabled', true)->orderBy('name')->get();

        $sections = Section::where('is_enabled', true)
            ->where('store_id', session('store')['id'])
            ->orderBy('name')
            ->get();

        $sinteticasSemAnaliticas = Section::where('type', 'S')
        ->whereNotIn('id', function ($query) {
            $query->select('parent_id')
                ->from('sections')
                ->where('type', 'A');
        })
        ->get();

        return view('products.edit', compact(
            'products',
            'item',
            'ums',
            'paymentMethods',
            'brands',
            'families',
            'presentations',
            'sections',
            'grids',
            'sinteticasSemAnaliticas'
        ));
    }

    public function update(Request $request, $id)
    {
        $item = Product::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();

        DB::transaction(function () use ($request, $item) {
            $inputs = $request->all();
            $inputs['brand_id'] = $request->filled('brand_id') ? $request->brand_id : null;

            $inputs['price'] = moneyToFloat($request->price);
            $inputs['discount'] = moneyToFloat($request->discount);
            $inputs['weight'] = moneyToFloat($request->weight);
            $inputs['cubic_weight'] = moneyToFloat($request->cubic_weight);
            $inputs['length'] = moneyToFloat($request->length);
            $inputs['width'] = moneyToFloat($request->width);
            $inputs['height'] = moneyToFloat($request->height);
            $inputs['promotion_price'] = moneyToFloat($request->promotion_price);

            $descriptionReference = $item->description_reference;

            $item->fill($inputs)->save();


            $imageIds   = $item->images->pluck('id')->all();
            $idsRequest     = $request->oldimages ?? [];

            $result = array_diff($imageIds, $idsRequest);
            ProductImage::whereIn('id', $result)->delete();
            if (!empty($request->images)) {
                foreach ($request->images as $key => $image) {
                    $upload = $image->store('products', 'public');
                    if (count($item->images) > $key) {
                        $image = $item->images[$key];
                        $image->name = $upload;
                        $image->save();
                    } else {
                        $item->images()->create(['name' => $upload]);
                    }
                }
            }

            if ($item->is_grid) {
                $productsReference = Product::where('reference', $item->reference)->get();

                foreach ($productsReference as $product) {
                    $replaced = Str::replace($descriptionReference, $request->description_reference, $product->commercial_name);

                    $product->update(
                        [
                            'commercial_name' => $replaced,
                            'description_reference' => $request->description_reference
                        ]
                    );
                }
            }


            $item->products()->sync($request->products);
            $item->paymentMethods()->sync($request->paymentMethods);
            $item->sections()->sync($request->sections);
        });


        return redirect()->route('products.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Product::findOrFail($id);

        try {
            DB::beginTransaction();
            $item->products()->detach();
            $item->variation()->detach();
            $item->paymentMethods()->detach();
            $item->sections()->detach();
            $item->images()->delete();
            $item->delete();
            DB::commit();
            return redirect()->route('products.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('products.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    public function resizeImage($image)
    {
        $name = uniqid();
        $file = $image;
        
        $filename = "{$name}.webp";
        
        if($file->getClientOriginalExtension() == 'gif'){
            $filename = "{$name}.gif";
        }
        
        $path = storage_path('app/public') . "/products";
        
        // Defina a pasta de destino onde a imagem será armazenada
        $destinationMobileFolder = storage_path('app/public') . "/products/mobile";
        
        if (!File::ensureDirectoryExists($destinationMobileFolder)) {
            Storage::makeDirectory($destinationMobileFolder);
        }
        
        if($file->getClientOriginalExtension() != 'gif'){
            // Crie uma instância do Intervention Image para a imagem carregada
            $img = Image::make($file)->encode('webp', 90);
            
            // Redimensione a imagem para a largura máxima especificada
            $img->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            

        // Salve a imagem redimensionada na pasta de destino com o nome gerado
        $img->save($path . '/' . $filename);

        // Redimensione a imagem para o mobile
        $img->resize(600, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $img->save($destinationMobileFolder . '/' . $filename);

        }else{
            Storage::disk('public')->put('products' . '/' . $filename, file_get_contents($file));
            Storage::disk('public')->put('products/mobile' . '/' . $filename, file_get_contents($file));
        }

        $filename = "products/" . $filename;

        return $filename;
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'video' => ['nullable', 'url', 'max:50'],
            'reference' => ['required', 'max:15'],
            'origin' => ['required', 'max:1', 'numeric'],
            'commercial_name' => ['required', 'max:60'],
            'description_reference' => [Rule::requiredIf(boolval($request->is_grid)), 'max:60'],
            'description' => ['nullable'],
            'um_id' => ['required'],
            'tag' => ['nullable'],
            'price' => ['required'],
            'promotion_price' => ['required'],
            'discount' => ['nullable'],
            'payment_condition' => ['nullable', 'max:30'],
            'weight' => ['nullable'],
            'width' => ['required'],
            'height' => ['required'],
            'length' => ['required'],
            'cubic_weight' => ['nullable'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'about' => ['nullable', 'max:200'],
            'recommendation' => ['nullable', 'max:200'],
            'benefits' => ['nullable', 'max:200'],
            'formula' => ['nullable', 'max:200'],
            'application_mode' => ['nullable', 'max:200'],
            'dosage' => ['nullable', 'max:200'],
            'lack' => ['nullable', 'max:60'],
            'other_information' => ['nullable', 'max:400'],
            'is_enabled' => ['required', 'boolean'],
            'type' => ['required'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
