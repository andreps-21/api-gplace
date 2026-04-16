<?php

namespace App\Http\Controllers\Admin;

use App\Models\Presentation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PresentationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:presentations_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:presentations_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:presentations_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:presentations_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Presentation::orderBy('name')->paginate(10);

        return view('presentations.index', compact('data'));
    }

    public function create()
    {
        return view('presentations.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        Presentation::create($request->all());

        return redirect()->route('presentations.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Presentation::findOrFail($id);

        return view('presentations.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Presentation::findOrFail($id);

        return view('presentations.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Presentation::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();


        $item->fill($request->all())->save();

        return redirect()->route('presentations.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Presentation::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('presentations.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('presentations.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required','max:20', Rule::unique('presentations')->ignore($primaryKey)],
            'detailing' => ['nullable','max:30'],
            'is_enabled' => ['required'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
