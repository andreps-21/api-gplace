<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Erp;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ErpController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:erp_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:erp_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:erp_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:erp_delete', ['only' => ['destroy']]);
    }


    public function index(Request $request)
    {
        $data = Erp::paginate(10);

        return view('erp.index', compact('data'));
    }

    public function show($id)
    {
        $item = Erp::findOrFail($id);

        return view('erp.show', compact('item'));
    }

    public function create()
    {
        return view('erp.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $item = new Erp($request->all());

        $item->save();

        return redirect()->route('erp.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function edit($id)
    {
        $item = Erp::findOrFail($id);

        return view('erp.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Erp::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();

        $item->fill($request->all())->save();

        return redirect()->route('erp.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Erp::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('erp.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('erp.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, bool $changeMessages = false)
    {
        $rules = [
            'description' => ['required', 'max:40'],
            'status' => ['required']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
