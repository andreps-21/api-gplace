<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterfacePosition;
use Illuminate\Support\Facades\Validator;
use App\Models\SizeImage;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SizeImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:size-image_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:size-image_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:size-image_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:size-image_delete', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $data = SizeImage::orderBy('name')
        ->when(!empty($request->size), function ($query) use ($request) {
            $query->where('size_images.name', $request->size);
        })
        ->when($request->is_active != null, function ($q) use ($request) {
            return $q->where('size_images.is_enabled', '=', $request->is_active);
        })
        ->when($request->type != null, function ($q) use ($request) {
            return $q->where('size_images.type', '=', $request->type);
        })
        ->when(!empty($request->position), function ($query) use ($request) {
            $query->whereHas('interfacePositions', function ($query) use ($request) {
                $query->where('interface_positions.id_position', $request->position);
            });
        })
        ->paginate(10);

        $size = SizeImage::orderBy('name')->get()->unique('name');

        $interfacePositions = InterfacePosition::orderBy('position_name')->pluck('position_name', 'id_position')->mapWithKeys(function ($name, $id) {
            return [$id => "$id - $name"];
        });

        return view('size-image.index', compact('data', 'size', 'interfacePositions'));
    }

    public function create()
    {
        $tenants = Tenant::person()->get();
        $interfacePositions = InterfacePosition::where('is_enabled', true)->pluck('position_name', 'id');

        return view('size-image.create', compact('tenants', 'interfacePositions'));
    }

    public function store(Request $request)
    {
        Validator::make(
            $request->all(),
            $this->rules($request)
        )->validate();

        $sizeImage = SizeImage::create($request->all());
        $sizeImage->tenants()->attach($request->tenants);
        $sizeImage->interfacePositions()->attach($request->interfacePositions);

        return redirect()->route('size-image.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = SizeImage::with('tenants')->findOrFail($id);

        return view('size-image.show', compact('item'));
    }

    public function edit($id)
    {
        $item = SizeImage::with('interfacePositions')
        ->where('size_images.id', $id)
        ->firstOrFail();

        $interfacePositions = InterfacePosition::where('is_enabled', true)->pluck('position_name', 'id');

        return view('size-image.edit', compact('item','interfacePositions'));
    }

    public function update(Request $request, $id)
    {
        $item = SizeImage::findOrFail($id);

        Validator::make(
            $request->all(),
            $this->rules($request, $item->getKey())
        )->validate();


        $item->fill($request->all())->save();
        $item->interfacePositions()->sync($request->interfacePositions);

        return redirect()->route('size-image.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = SizeImage::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('size-image.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('size-image.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    public function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name'=> ['required','max:50'],
            'size_width'=> ['required','max:5'],
            'size_height'=> ['required','max:5'],
            'is_enabled'=> ['required'],
            //'code' => ['required', Rule::unique('size_images')->ignore($primaryKey)],
            'type' => ['required']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
