<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Tenant;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:brands_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:brands_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:brands_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:brands_delete', ['only' => ['destroy']]);
        $this->middleware('tenant');
    }

    public function index(Request $request)
    {
        $data =  Brand::query()
            ->where('tenant_id', session('tenant')['id'])
            ->orderBy('name')
            ->paginate(10);

        return view('brands.index', compact('data'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $item = new Brand($request->all());
        $item->tenant_id = session('tenant')['id'];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $upload = $request->file('image')->store('brands', 'public');

            $item->image = $upload;
        }

        $item->save();

        return redirect()->route('brands.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Brand::with('tenant.people')->findOrFail($id);

        return view('brands.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Brand::findOrFail($id);

        return view('brands.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Brand::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();


        $item->fill($request->except('image'));

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $upload = $request->file('image')->store('payment-methods', 'public');

            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $item->image = $upload;
        }

        $item->save();

        return redirect()->route('brands.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Brand::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('brands.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('brands.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => [
                'required', 'max:30', 'min:3',
                Rule::unique('brands')->where('tenant_id', session('tenant')['id'])->ignore($primaryKey)
            ],
            'is_enabled' => ['required', 'boolean'],
            'is_public' => ['required', 'boolean'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
