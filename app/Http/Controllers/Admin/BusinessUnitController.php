<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessUnit;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessUnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:businessunits_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:businessunits_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:businessunits_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:businessunits_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $data = BusinessUnit::query()
        ->city()
        ->where('tenant_id', session('tenant')['id'])
        ->paginate(10);

        return view('businessunits.index', compact('data'));
    }

    public function show($id)
    {
        $item = BusinessUnit::city()->findOrFail($id);

        return view('businessunits.show', compact('item'));
    }

    public function create()
    {
        return view('businessunits.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $inputs = $request->all();
        $inputs['tenant_id'] = session('tenant')['id'];

        BusinessUnit::create($inputs);

        return redirect()->route('business-units.index')
            ->withStatus('Registro criado com sucesso.');
    }

    public function edit($id)
    {
        $item = BusinessUnit::city()->findOrFail($id);

        return view('businessunits.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $item = BusinessUnit::findOrFail($id);

        $item->fill($request->all())->save();

        return redirect()->route('business-units.index')
            ->withStatus('Registro editado com sucesso.');
    }

    public function destroy($id)
    {
        $item = BusinessUnit::findOrFail($id);

        try {
            $item->delete();

            return redirect()->route('business-units.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('business-units.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:30'],
            'description' => ['required', 'max:60'],
            'is_enabled' => ['required'],
            'city_id' => ['required'],
            'zip_code_start' => ['required', 'max:9'],
            'zip_code_end' => ['required', 'max:9']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
