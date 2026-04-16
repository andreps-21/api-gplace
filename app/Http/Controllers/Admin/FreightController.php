<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Freight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FreightController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:freights_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:freights_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:freights_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:freights_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $data = Freight::query()
            ->city()
            ->where('store_id', session('store')['id'])
            ->paginate(10);

        return view('freights.index', compact('data'));
    }

    public function show($id)
    {
        $item = Freight::city()->findOrFail($id);

        return view('freights.show', compact('item'));
    }

    public function create()
    {
        return view('freights.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $inputs = $request->all();
        $inputs['percentage'] = moneyToFloat($request->percentage);
        $inputs['value_freight_fix'] = moeda($request->value_freight_fix);
        $inputs['free_shipping_sales'] = moeda($request->free_shipping_sales);
        $inputs['store_id'] = session('store')['id'];
        Freight::create($inputs);

        return redirect()->route('freights.index')
            ->withStatus('Registro criado com sucesso.');
    }

    public function edit($id)
    {
        $item = Freight::city()->findOrFail($id);

        return view('freights.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $item = Freight::findOrFail($id);

        $inputs = $request->all();
        $inputs['value_freight_fix'] = moeda($request->value_freight_fix);
        $inputs['free_shipping_sales'] = moeda($request->free_shipping_sales);
        $inputs['percentage'] = moneyToFloat($request->percentage);

        $item->fill($inputs)->save();

        return redirect()->route('freights.index')
            ->withStatus('Registro editado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Freight::findOrFail($id);

        try {
            $item->delete();

            return redirect()->route('freights.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception$e) {
            return redirect()->route('freights.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:30'],
            'city_id' => ['required'],
            'is_enabled' => ['required'],
            'description' => ['required', 'max:60'],
            'zip_code_start' => ['required', 'max:9'],
            'zip_code_end' => ['required', 'max:9'],
            'notes' => ['nullable', 'max:150'],
            'percentage' => ['required'],
            'value_freight_fix' => ['nullable'],
            'free_shipping_sales' => ['nullable'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
