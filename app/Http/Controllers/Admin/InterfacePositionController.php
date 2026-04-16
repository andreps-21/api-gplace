<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterfacePosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InterfacePositionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:interface-positions_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:interface-positions_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:interface-positions_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:interface-positions_delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $data =  InterfacePosition::orderBy('id')
        ->paginate(10);

        return view('interface-positions.index', compact('data'));
    }

    public function create()
    {
        $instance = (new InterfacePosition())->getInstance();
        return view('interface-positions.create', compact('instance'));
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        InterfacePosition::create($request->all());

        return redirect()->route('interface-positions.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = InterfacePosition::findOrFail($id);

        return view('interface-positions.show', compact('item'));
    }

    public function edit($id)
    {
        $item = InterfacePosition::findOrFail($id);

        return view('interface-positions.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = InterfacePosition::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();


        $item->fill($request->all())->save();

        return redirect()->route('interface-positions.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = InterfacePosition::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('interface-positions.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('interface-positions.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    public function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'id_position' => ['required'],
            'position_name'=> ['required','max:60'],
            'is_enabled'=> ['required']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
