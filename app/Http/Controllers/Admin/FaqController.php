<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:faq_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:faq_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:faq_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:faq_delete', ['only' => ['destroy']]);
        $this->middleware('store');
    }
    public function index(Request $request)
    {
        $data =  Faq::where('store_id', session('store')['id'])->orderBy('question')->paginate(10);

        return view('faq.index', compact('data'));
    }

    public function create(Request $request)
    {

        return view('faq.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $inputs = $request->all();
        $inputs['store_id'] = session('store')['id'];
        Faq::create($inputs);

        return redirect()->route('faq.index')
            ->withStatus('Registro criado com sucesso.');
    }

    public function show($id)
    {
        $item = Faq::findOrFail($id);

        return view('faq.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Faq::findOrFail($id);

        return view('faq.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Faq::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();


        $item->fill($request->all())->save();

        return redirect()->route('faq.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Faq::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('faq.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('faq.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'question' => ['required', 'max:40'],
            'answer' => ['required'],
            'url' => ['nullable', 'url'],
            'is_enabled' => ['required']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
