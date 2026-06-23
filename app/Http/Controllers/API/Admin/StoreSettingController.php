<?php

namespace App\Http\Controllers\API\Admin;

use App\Enums\SettingsStatus;
use App\Http\Controllers\API\BaseController;
use App\Models\Erp;
use App\Models\Setting;
use App\Models\SocialMedia;
use App\Models\Store;
use App\Rules\CpfCnpj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

/**
 * Configurações da loja (equivalente ao Blade settings.edit), escopo pela loja do header {@see CheckAppHeader}.
 */
class StoreSettingController extends BaseController
{
    public function show(Request $request)
    {
        $store = $request->attributes->get('store');
        $storeId = (int) $store['id'];

        $settings = Setting::query()
            ->where('store_id', $storeId)
            ->with(['city.state', 'socialMedias', 'erps'])
            ->first();

        $settings = $this->settingsWithStoreDefaults($storeId, $settings);

        return $this->sendResponse([
            'settings' => $settings,
            'social_media_options' => SocialMedia::query()->where('is_enabled', true)->get(['id', 'description']),
            'erp_options' => Erp::query()->where('status', true)->get(['id', 'description']),
        ]);
    }

    public function update(Request $request)
    {
        $store = $request->attributes->get('store');
        $storeId = (int) $store['id'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:35'],
            'full_name' => ['required', 'string', 'max:50'],
            'nif' => ['required', new CpfCnpj()],
            'city_id' => ['required', 'integer'],
            'address' => ['required', 'string', 'max:50'],
            'number' => ['required', 'string', 'max:5'],
            'district' => ['nullable', 'string', 'max:35'],
            'maps' => ['nullable', 'string', 'max:150'],
            'contact' => ['nullable', 'string', 'max:30'],
            'zip_code' => ['required', 'string', 'max:9'],
            'email' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:15'],
            'status' => ['required', Rule::in(array_keys(SettingsStatus::status()))],
            'note' => ['nullable', 'string', 'max:200'],
            'portal_url' => ['required', 'url'],
            'email_notification' => ['required', 'string', 'max:50'],
            'whatsapp_phone' => ['required', 'string', 'max:15'],
            'terms' => ['nullable', 'string'],
            'privacy_policy' => ['nullable', 'string'],
            'footer' => ['nullable', 'string', 'max:200'],
            'footer_background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'footer_text_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'meta_tags' => ['nullable', 'string', 'max:200'],
            'pixels' => ['nullable', 'string', 'max:80'],
            'ads' => ['nullable', 'string', 'max:80'],
            'cookies' => ['nullable', 'string'],
            'instagram_url' => ['nullable', 'string', 'max:80'],
            'instagram_user' => ['nullable', 'string', 'max:40'],
            'instagram_password' => ['nullable', 'string', 'max:15'],
            'facebook_url' => ['nullable', 'string', 'max:80'],
            'facebook_user' => ['nullable', 'string', 'max:40'],
            'facebook_password' => ['nullable', 'string', 'max:15'],
            'youtube_url' => ['nullable', 'string', 'max:80'],
            'youtube_user' => ['nullable', 'string', 'max:40'],
            'youtube_password' => ['nullable', 'string', 'max:15'],
            'twitter_url' => ['nullable', 'string', 'max:80'],
            'twitter_user' => ['nullable', 'string', 'max:40'],
            'twitter_password' => ['nullable', 'string', 'max:15'],
            'payment_gateway' => ['nullable', 'string'],
            'payment_info' => ['nullable', 'array'],
            'freight_gateway' => ['nullable', 'string'],
            'freight_info' => ['nullable', 'array'],
            'pix_gateway' => ['nullable', 'string'],
            'pix_info' => ['nullable', 'array'],
            'integration_info' => ['nullable', 'array'],
            'android_ver' => ['nullable', 'string', 'max:20'],
            'apple_ver' => ['nullable', 'string', 'max:20'],
            'android_url_store' => ['nullable', 'string', 'max:255'],
            'apple_url_store' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
            'logo_footer' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
            'favicon' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:1024'],
        ]);

        $validated['store_id'] = $storeId;

        $settings = null;

        DB::transaction(function () use ($request, $storeId, $validated, &$settings) {
            $storeModel = Store::query()->with('people')->findOrFail($storeId);
            $storeModel->fill([
                'status' => $validated['status'],
            ])->save();

            $storeModel->people?->fill([
                'name' => $validated['name'],
                'formal_name' => $validated['full_name'],
                'nif' => $validated['nif'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'city_id' => $validated['city_id'],
                'street' => $validated['address'],
                'address' => $validated['address'],
                'zip_code' => $validated['zip_code'],
                'number' => $validated['number'],
                'district' => $validated['district'] ?? null,
            ])->save();

            $settings = Setting::query()->firstOrNew(['store_id' => $storeId]);
            $settings->fill($validated);

            if ($request->hasFile('logo')) {
                if ($settings->logo) {
                    Storage::disk('public')->delete($settings->logo);
                }
                $settings->logo = $request->file('logo')->store('store-assets/logos', 'public');
            }

            if ($request->hasFile('logo_footer')) {
                if ($settings->logo_footer) {
                    Storage::disk('public')->delete($settings->logo_footer);
                }
                $settings->logo_footer = $request->file('logo_footer')->store('store-assets/logos', 'public');
            }

            if ($request->hasFile('favicon')) {
                if ($settings->favicon) {
                    Storage::disk('public')->delete($settings->favicon);
                }
                $settings->favicon = $this->storeFavicon($request->file('favicon'), $storeId);
            }

            $settings->save();
        });

        Cache::forget("cms-home-{$storeId}");

        $settings->load(['city.state', 'socialMedias', 'erps']);
        $settings->makeVisible(['pix_info']);
        $settings->append(['logo_url', 'logo_footer_url', 'favicon_url']);

        return $this->sendResponse($settings, 'Configurações atualizadas.', 200);
    }

    private function settingsWithStoreDefaults(int $storeId, ?Setting $settings): Setting
    {
        $store = Store::query()
            ->with('people.city.state')
            ->find($storeId);

        $storeDefaults = [
            'store_id' => $storeId,
            'name' => $store?->people?->name ?? '',
            'full_name' => $store?->people?->formal_name ?? $store?->people?->name ?? '',
            'nif' => $store?->people?->nif ?? '',
            'city_id' => $store?->people?->city_id,
            'address' => $store?->people?->street ?? $store?->people?->address ?? '',
            'number' => $store?->people?->number ?? '',
            'district' => $store?->people?->district ?? '',
            'zip_code' => $store?->people?->zip_code ?? '',
            'email' => $store?->people?->email ?? '',
            'phone' => $store?->people?->phone ?? '',
            'status' => (string) ($store?->status ?? 1),
            'whatsapp_phone' => $store?->people?->phone ?? '',
            'email_notification' => $store?->people?->email ?? '',
        ];

        $fallbackDefaults = [
            'portal_url' => url('/'),
            'footer_background_color' => '#1e293b',
            'footer_text_color' => '#ffffff',
            'brand_color' => '#0284c7',
        ];

        $settings ??= new Setting(['store_id' => $storeId]);

        foreach ($storeDefaults as $key => $value) {
            $settings->{$key} = $value ?? '';
        }

        foreach ($fallbackDefaults as $key => $value) {
            if (($settings->{$key} === null || $settings->{$key} === '') && $value !== null && $value !== '') {
                $settings->{$key} = $value;
            }
        }

        if (! $settings->relationLoaded('city') && $settings->city_id) {
            $settings->load('city.state');
        }

        $settings->makeVisible(['pix_info']);
        $settings->append(['logo_url', 'logo_footer_url', 'favicon_url']);

        return $settings;
    }

    private function storeFavicon($file, int $storeId): string
    {
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));

        if (! $source) {
            throw ValidationException::withMessages([
                'favicon' => ['Não foi possível converter a imagem enviada para favicon.'],
            ]);
        }

        $size = 32;
        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $size,
            $size,
            imagesx($source),
            imagesy($source)
        );

        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if ($png === false) {
            throw ValidationException::withMessages([
                'favicon' => ['Não foi possível gerar o favicon.'],
            ]);
        }

        $ico = pack('vvv', 0, 1, 1)
            . pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($png), 22)
            . $png;

        $path = 'store-assets/favicons/store-' . $storeId . '-' . uniqid('', true) . '.ico';
        Storage::disk('public')->put($path, $ico);

        return $path;
    }
}
