<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TokenController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tokens_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:tokens_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:tokens_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:tokens_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Token::orderBy('description')
            ->paginate(10);

        return view('tokens.index', compact('data'));
    }

    public function create()
    {
        $stores = Store::person()
            ->orderby('name')
            ->get();

        return view('tokens.create', compact('stores'));
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $inputs = $request->all();
        $inputs['access_token'] = hash('sha256', Str::random(40));
        $inputs['expires_at'] = now()->addYear();
        Token::create($inputs);

        return redirect()->route('tokens.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Token::with('store.people')->findOrFail($id);

        return view('tokens.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Token::findOrFail($id);

        $stores = Store::person()
            ->orderby('name')
            ->get();

        return view('tokens.edit', compact('item', 'stores'));
    }

    public function update(Request $request, $id)
    {
        $item = Token::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();


        $item->fill($request->all())->save();

        return redirect()->route('tokens.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Token::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('tokens.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('tokens.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'description' => ['required', 'max:50', Rule::unique('tokens')->ignore($primaryKey)],
            'store_id' => 'required'
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
