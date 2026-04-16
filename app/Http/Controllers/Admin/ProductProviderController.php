<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductProvider;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:product-providers_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:product-providers_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:product-providers_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:product-providers_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = ProductProvider::info()
            ->orderBy('products.commercial_name')
            ->paginate(10);

        return view('product-providers.index', compact('data'));
    }

    public function create()
    {
        $products = Product::query()
            ->select(
                'products.id',
                'products.commercial_name',
                'measurement_units.initials'
            )
            ->join('measurement_units', 'measurement_units.id', '=', 'products.um_id')
            ->where('products.is_enabled',  true)
            ->where('products.type',  'S')
            ->orderBy('products.commercial_name')
            ->get();

        $providers = Provider::person()
            ->where('providers.type', 1)
            ->where('providers.status', 2)
            ->orderBy('people.name')
            ->get();

        return view('product-providers.create', compact('products', 'providers'));
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request),
            $this->rules($request, null, true)
        )->validate();

        $inputs = $request->all();
        $inputs['price'] = moneyToFloat($request->price);
        $inputs['vl_km'] = moneyToFloat($request->vl_km);
        $inputs['vl_transfer'] = moneyToFloat($request->vl_transfer);

        ProductProvider::create($inputs);

        return redirect()->route('product-providers.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = ProductProvider::info()->findOrFail($id);

        return view('product-providers.show', compact('item'));
    }

    public function edit($id)
    {
        $item = ProductProvider::findOrFail($id);

        $products = Product::query()
            ->select(
                'products.id',
                'products.commercial_name',
                'measurement_units.initials'
            )
            ->join('measurement_units', 'measurement_units.id', '=', 'products.um_id')
            ->where('products.is_enabled',  true)
            ->where('products.type',  'S')
            ->orderBy('products.commercial_name')
            ->get();

        $providers = Provider::person()
            ->where('providers.type', 1)
            ->where('providers.status', 2)
            ->orderBy('people.name')
            ->get();

        return view('product-providers.edit', compact('item', 'products', 'providers'));
    }

    public function update(Request $request, $id)
    {
        $item = ProductProvider::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $id),
            $this->rules($request, $id, true)
        )->validate();

        $inputs = $request->all();
        $inputs['price'] = moneyToFloat($request->price);
        $inputs['vl_km'] = moneyToFloat($request->vl_km);
        $inputs['vl_transfer'] = moneyToFloat($request->vl_transfer);
        $item->fill($inputs)->save();

        return redirect()->route('product-providers.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = ProductProvider::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('product-providers.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('product-providers.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'product_id' => ['required', Rule::unique('product_providers')
                ->where('product_id', $request->product_id)
                ->where('provider_id', $request->provider_id)
                ->ignore($primaryKey)],
            'um' => ['required'],
            'provider_id' => ['required'],
            'price' => ['required'],
            'vl_km' => ['required'],
            'vl_transfer' => ['required'],
            'is_enabled' => ['required'],
        ];

        $messages = [
            'product_id.unique' => 'Já existe esse serviço vinculado a esse fornecedor.'
        ];

        return !$changeMessages ? $rules : $messages;
    }
}
