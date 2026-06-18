<?php

namespace App\Http\Controllers\API\Admin;

use App\Enums\TypeImage;
use App\Http\Controllers\API\BaseController;
use App\Models\Banner;
use App\Models\SizeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BannerAdminController extends BaseController
{
    private function storeId(Request $request): int
    {
        return (int) $request->get('store')['id'];
    }

    public function index(Request $request)
    {
        $query = Banner::query()
            ->with('sizeImages.pivot.interfacePosition')
            ->where('store_id', $this->storeId($request))
            ->orderBy('sequence')
            ->orderByDesc('id');

        return $this->sendResponse(
            $request->boolean('all') ? $query->get() : $query->paginate((int) $request->get('per_page', 25))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $placements = $data['placements'];
        unset($data['placements'], $data['image']);

        $data['store_id'] = $this->storeId($request);
        $data['filename'] = $request->file('image')->store('banners', 'public');

        $banner = Banner::create($data);
        $this->syncPlacements($banner, $placements);
        $this->forgetHomeCache($request);

        return $this->sendResponse($banner->fresh('sizeImages.pivot.interfacePosition'), 'Registro criado com sucesso.', 201);
    }

    public function show(Request $request, int $id)
    {
        $banner = Banner::with('sizeImages.pivot.interfacePosition')
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        return $this->sendResponse($banner);
    }

    public function update(Request $request, int $id)
    {
        $banner = Banner::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        $data = $request->validate($this->rules($id));
        $placements = $data['placements'];
        unset($data['placements'], $data['image']);

        if ($request->hasFile('image')) {
            if ($banner->filename) {
                Storage::disk('public')->delete($banner->filename);
            }
            $data['filename'] = $request->file('image')->store('banners', 'public');
        }

        $banner->fill($data)->save();
        $this->syncPlacements($banner, $placements);
        $this->forgetHomeCache($request);

        return $this->sendResponse($banner->fresh('sizeImages.pivot.interfacePosition'), 'Registro atualizado com sucesso.');
    }

    public function destroy(Request $request, int $id)
    {
        $banner = Banner::query()
            ->where('store_id', $this->storeId($request))
            ->findOrFail($id);

        if ($banner->filename) {
            Storage::disk('public')->delete($banner->filename);
        }

        $banner->sizeImages()->detach();
        $banner->delete();
        $this->forgetHomeCache($request);

        return $this->sendResponse([], 'Registro deletado com sucesso.');
    }

    public function options()
    {
        $placements = SizeImage::query()
            ->join('interface_position_size_images', 'size_images.id', '=', 'interface_position_size_images.size_image_id')
            ->join('interface_positions', 'interface_positions.id', '=', 'interface_position_size_images.interface_position_id')
            ->where('size_images.is_enabled', true)
            ->where('interface_positions.is_enabled', true)
            ->where('size_images.type', TypeImage::MEDIA)
            ->orderBy('interface_positions.id_position')
            ->orderBy('size_images.name')
            ->get([
                'size_images.id as size_image_id',
                'size_images.name as size_image_name',
                'size_images.size_width',
                'size_images.size_height',
                'interface_positions.id as interface_position_id',
                'interface_positions.id_position',
                'interface_positions.position_name',
            ]);

        return $this->sendResponse([
            'types' => Banner::types(),
            'placements' => $placements,
        ]);
    }

    private function rules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'url' => ['nullable', 'string', 'max:191'],
            'is_enabled' => ['required', 'boolean'],
            'type' => ['required', 'integer', Rule::in(array_keys(Banner::types()))],
            'sequence' => ['required', 'integer', 'min:0', 'max:255'],
            'image' => [$id ? 'nullable' : 'required', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
            'placements' => ['required', 'array', 'min:1'],
            'placements.*.size_image_id' => ['required', 'integer', 'exists:size_images,id'],
            'placements.*.interface_position_id' => ['required', 'integer', 'exists:interface_positions,id'],
        ];
    }

    private function syncPlacements(Banner $banner, array $placements): void
    {
        $banner->sizeImages()->detach();

        foreach ($placements as $placement) {
            $banner->sizeImages()->attach((int) $placement['size_image_id'], [
                'interface_position_id' => (int) $placement['interface_position_id'],
            ]);
        }
    }

    private function forgetHomeCache(Request $request): void
    {
        Cache::forget('cms-home-' . $this->storeId($request));
    }
}
