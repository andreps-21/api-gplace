<?php

namespace App\Http\Controllers\Admin;

use App\Rules\CpfCnpj;
use App\Models\ServiceArea;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ServiceAreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:services-area_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:services-area_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:services-area_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:services-area_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = ServiceArea::orderBy('description')->paginate(10);

        return view('services-area.index', compact('data'));
    }

    public function create()
    {
        return view('services-area.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        ServiceArea::create($request->all());

        return redirect()->route('services-area.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = ServiceArea::findOrFail($id);

        return view('services-area.show', compact('item'));
    }

    public function edit($id)
    {
        $item = ServiceArea::findOrFail($id);

        return view('services-area.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = ServiceArea::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();

        $item->fill($request->all())->save();

        return redirect()->route('services-area.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = ServiceArea::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('services-area.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('services-area.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'description'=> ['required', 'max:35', Rule::unique('services_area')->ignore($primaryKey)],
            'is_enabled'=> ['required', 'boolean'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
