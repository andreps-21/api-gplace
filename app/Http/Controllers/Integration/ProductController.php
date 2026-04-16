<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends BaseController
{
    public function index(Request $request)
    {
        $store = $request->get('store')['id'];

        $products = Product::query()
            ->orderBy('commercial_name')
            ->where('store_id', $store)
            ->with('brand', 'images', 'sections', 'measurement', 'section')
            ->paginate(25);

        return $this->sendResponse($products);
    }

    public function store(Request $request)
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request) {
            $inputs = $request->all();
            $inputs['store_id'] = $request->get('store')['id'];
            $inputs['origin'] = 0;
            $inputs['type'] = 'P';
            $product =  Product::create($inputs);

            if (!empty($request->images)) {
                foreach ($request->images as $image) {

                    $upload = $image->store('products', 'public');

                    $product->images()->create(['name' => $upload]);
                }
            }

            if (!empty($request->paymentMethods)) {
                $product->paymentMethods()->attach($request->paymentMethods);
            }

            if (!empty($request->sections)) {
                $product->sections()->attach($request->sections);
            }
        });

        return $this->sendResponse([], "Registro criado com sucesso.");
    }

    public function show(Request $request, $id)
    {
        $store = $request->get('store')['id'];

        $product = Product::query()
            ->where('store_id', $store)
            ->where('id', $id)
            ->with('brand', 'images', 'sections', 'measurement', 'section')
            ->firstOrFail();

        return $this->sendResponse($product);
    }

    public function update(Request $request, $id)
    {
        $store = $request->get('store')['id'];

        $product = Product::query()
            ->where('store_id', $store)
            ->where('id', $id)
            ->firstOrFail();


        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request, $id)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        DB::transaction(function () use ($request, $product) {
            $inputs = $request->all();
            $product->fill($inputs)->save();


            if (!empty($request->images)) {
                foreach ($request->images as $key => $image) {
                    $upload = $image->store('products', 'public');
                    if (count($product->images) > $key) {
                        $image = $product->images[$key];
                        $image->name = $upload;
                        $image->save();
                    } else {
                        $product->images()->create(['name' => $upload]);
                    }
                }
            }

            if (!empty($request->paymentMethods)) {
                $product->paymentMethods()->sync($request->paymentMethods);
            }

            if (!empty($request->sections)) {
                $product->sections()->sync($request->sections);
            }
        });

        return $this->sendResponse([], "Registro atualizado com sucesso.");
    }

    public function destroy(Request $request, $id)
    {
        $store = $request->get('store')['id'];

        $product = Product::query()
            ->where('store_id', $store)
            ->where('id', $id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $product->images()->delete();
            $product->products()->detach();
            $product->sections()->detach();
            $product->paymentMethods()->detach();
            $product->delete();
            DB::commit();
            return $this->sendResponse([], "Registro deletado com sucesso.");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError("Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.", [], 403);
        }
    }




    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'video' => ['nullable', 'url', 'max:50'],
            'reference' => ['required', 'max:15'],
            'commercial_name' => ['required', 'max:60'],
            'description' => ['nullable', 'max:255'],
            'um_id' => ['required'],
            'tag' => ['nullable'],
            'price' => ['required', 'numeric'],
            'promotion_price' => ['required', 'numeric'],
            'discount' => ['nullable'],
            'payment_condition' => ['nullable', 'max:30'],
            'weight' => ['required', 'numeric'],
            'width' => ['required', 'numeric'],
            'height' => ['required', 'numeric'],
            'length' => ['required', 'numeric'],
            'cubic_weight' => ['nullable', 'numeric'],
            'brand_id' => ['required', 'exists:brands,id'],
            'is_enabled' => ['required', 'boolean'],
            'sections' => ['nullable', 'array'],
            'sections.*' => ['required', 'exists:sections,id'],
            'paymentMethods' => ['required', 'array'],
            'paymentMethods.*' => ['required', 'exists:payment_methods,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:1000']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
