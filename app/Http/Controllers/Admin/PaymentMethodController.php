<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:payment-methods_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payment-methods_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payment-methods_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:payment-methods_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = PaymentMethod::paginate(10);

        return view('payment-methods.index', compact('data'));
    }

    public function create()
    {
        return view('payment-methods.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $item = new PaymentMethod($request->all());

        if ($request->hasFile('icon') && $request->file('icon')->isValid()) {

            $upload = $request->file('icon')->store('payment-methods', 'public');

            $item->icon = $upload;
        }

        $item->save();

        return redirect()->route('payment-methods.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = PaymentMethod::findOrFail($id);

        return view('payment-methods.show', compact('item'));
    }

    public function edit($id)
    {
        $item = PaymentMethod::findOrFail($id);

        return view('payment-methods.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = PaymentMethod::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();

        $item->fill($request->except('icon'));

        if ($request->hasFile('icon') && $request->file('icon')->isValid()) {

            $upload = $request->file('icon')->store('payment-methods', 'public');

            if ($item->icon) {
                Storage::disk('public')->delete($item->icon);
            }

            $item->icon = $upload;
        }
        $item->save();

        return redirect()->route('payment-methods.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = PaymentMethod::findOrFail($id);

        try {
            if ($item->icon) {
                Storage::disk('public')->delete($item->icon);
            }
            $item->delete();
            return redirect()->route('payment-methods.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('payment-methods.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'icon' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'description' => ['required', 'max:30', Rule::unique('payment_methods')->ignore($primaryKey)],
            'code' => ['required', 'max:30'],
            'is_enabled' => ['required']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
