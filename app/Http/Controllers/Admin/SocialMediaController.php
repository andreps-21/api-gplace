<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SocialMediaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:social-medias_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:social-medias_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:social-medias_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:social-medias_delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = SocialMedia::paginate(10);

        return view('social-medias.index', compact('data'));
    }

    public function create()
    {
        return view('social-medias.create');
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $item = new SocialMedia($request->all());

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $upload = $request->file('image')->store('social-medias', 'public');

            $item->icon = $upload;
        }

        $item->save();

        return redirect()->route('social-medias.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = SocialMedia::findOrFail($id);

        return view('social-medias.show', compact('item'));
    }

    public function edit($id)
    {
        $item = SocialMedia::findOrFail($id);

        return view('social-medias.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = SocialMedia::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();

        $item->fill($request->except('icon'));

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $upload = $request->file('image')->store('social-medias', 'public');

            if ($item->icon) {
                Storage::disk('public')->delete($item->icon);
            }

            $item->icon = $upload;
        }
        $item->save();

        return redirect()->route('social-medias.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = SocialMedia::findOrFail($id);

        try {
            if ($item->icon) {
                Storage::disk('public')->delete($item->icon);
            }
            $item->delete();
            return redirect()->route('social-medias.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('social-medias.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'icon' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'description' => ['required', 'max:30'],
            'code' => ['required', 'max:30', Rule::unique('social_media')->ignore($primaryKey)],
            'is_enabled' => ['required']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
