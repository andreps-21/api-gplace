<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TypeImage;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\InterfacePosition;
use App\Models\SizeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;


class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:banners_create', ['only' => ['create', 'store']]);
        $this->middleware('permission:banners_edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:banners_view', ['only' => ['show', 'index']]);
        $this->middleware('permission:banners_delete', ['only' => ['destroy']]);
        $this->middleware('store');
    }

    public function index(Request $request)
    {
        $data = Banner::with('sizeImages.pivot.interfacePosition')->where('store_id', session('store')['id'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data->each(function ($item) {
            $ids = $item->sizeImages->pluck('pivot.interfacePosition.id_position')->toArray();
            $names = $item->sizeImages->pluck('pivot.interfacePosition.position_name')->toArray();
            $idNames = array_map(function ($id, $name) {
                return "$id - $name";
            }, $ids, $names);

            $item->position_data = implode(', ', $idNames);
        });

        return view('banners.index', compact('data'));
    }

    public function create()
    {
        $sizeImages = SizeImage::query()
            ->select(
                DB::raw('concat(size_images.id, " - ", interface_positions.id) as code'),
                DB::raw('concat(interface_positions.id_position, " - ",interface_positions.position_name, " - ", size_images.name) as name')
            )
            ->join('interface_position_size_images', 'size_images.id', 'interface_position_size_images.size_image_id')
            ->join('interface_positions', 'interface_positions.id', 'interface_position_size_images.interface_position_id')
            ->where('size_images.is_enabled', true)
            ->where('size_images.type', TypeImage::MEDIA)
            ->orderBy('interface_positions.position_name')
            ->get();

        return view('banners.create', compact('sizeImages'));
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            $this->rules($request)
        );

        $inputs = $request->all();
        $inputs['store_id'] = session('store')['id'];

        $item = new Banner($inputs);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $file = $request->file('image');

            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            try{
                Storage::disk('public')->put('banners' . '/' . $filename, file_get_contents($file));
                Storage::disk('public')->put('banners/mobile' . '/' . $filename, file_get_contents($file));
            } catch (\Exception $e) {
                Log::error("erro ao salvar Banner" . $e->getMessage());
                return redirect()->route('banners.index')
                ->withErrors('Ocorreu um erro ao fazer Upload da Imagem.');
            }

            $item->filename = 'banners/' . $filename;
        }

        $item->save();

        foreach($request->sizeImages as $sizeImage){
            list($sizeImage, $interfacePosition) = explode(" - ", $sizeImage);
            $item->sizeImages()->attach($sizeImage, ['interface_position_id' => $interfacePosition] );
        }

        return redirect()->route('banners.index')
            ->withStatus('Registro adicionado com sucesso.');
    }

    public function show($id)
    {
        $item = Banner::with('sizeImages.pivot.interfacePosition')
        ->where('store_id', session('store')['id'])
        ->where('banners.id', $id)
        ->firstOrFail();

        $item->position_names = $item->sizeImages->pluck('pivot.interfacePosition.position_name')->implode(', ');

        return view('banners.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Banner::with('sizeImages')
        ->where('store_id', session('store')['id'])
        ->where('banners.id', $id)
        ->firstOrFail();

        $item->sizeImages = $item->sizeImages->map(function ($sizeImage){
            $sizeImage->code = $sizeImage->id . " - " . $sizeImage->pivot->interface_position_id;
            return $sizeImage;
        });

        $sizeImages = SizeImage::query()
            ->select(
                DB::raw('concat(size_images.id, " - ", interface_positions.id) as code'),
                DB::raw('concat(interface_positions.id_position, " - ", interface_positions.position_name, " - ", size_images.name) as name')
            )
            ->join('interface_position_size_images', 'size_images.id', 'interface_position_size_images.size_image_id')
            ->join('interface_positions', 'interface_positions.id', 'interface_position_size_images.interface_position_id')
            ->where('size_images.is_enabled', true)
            ->where('size_images.type', TypeImage::MEDIA)
            ->orderBy('interface_positions.position_name')
            ->get();

        return view('banners.edit', compact('item', 'sizeImages'));
    }

    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            $this->rules($request, $id)
        );

        $item = Banner::findOrFail($id);

        $inputs = $request->all();
        $inputs['store_id'] = session('store')['id'];

        $item->fill($inputs);

        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $file = $request->file('image');

            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            try{
                Storage::disk('public')->put('banners' . '/' . $filename, file_get_contents($file));
                Storage::disk('public')->put('banners/mobile' . '/' . $filename, file_get_contents($file));
            } catch (\Exception $e) {
                Log::error("erro ao salvar Banner. Banner ID:". $item->id . $e->getMessage());
                return redirect()->route('banners.index')
                ->withErrors('Ocorreu um erro ao fazer Upload da Imagem.');
            }

            if ($item->filename) {
                Storage::delete($item->filename);
            }
            $item->filename = 'banners/' . $filename;
        }

        $item->save();

        $item->sizeImages()->detach();

        foreach($request->sizeImages as $sizeImage){
            list($sizeImage, $interfacePosition) = explode(" - ", $sizeImage);
            $item->sizeImages()->attach($sizeImage, ['interface_position_id' => $interfacePosition] );
        }

        return redirect()->route('banners.index')
            ->withStatus('Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $item = Banner::where('store_id', session('store')['id'])
        ->where('banners.id', $id)
        ->firstOrFail();

        try {
            if ($item->filename) {
                Storage::disk('public')->delete($item->filename);
            }
            $item->delete();
            return redirect()->route('banners.index')
                ->withStatus('Registro deletado com sucesso.');
        } catch (\Exception $e) {
            return redirect()->route('banners.index')
                ->withError('Registro vinculado á outra tabela, somente poderá ser excluído se retirar o vinculo.');
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:100'],
            'is_enabled' => ['required'],
            'type' => ['required'],
            'sequence' => ['required'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,svg'],
        ];

        if (empty($primaryKey)) {
            $rules['image'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg'];
        }

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
