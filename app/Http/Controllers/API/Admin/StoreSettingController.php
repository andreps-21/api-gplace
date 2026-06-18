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
use Illuminate\Support\Facades\Storage;
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
        ]);

        $validated['store_id'] = $storeId;

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

        $settings->save();
        Cache::forget("cms-home-{$storeId}");

        $settings->load(['city.state', 'socialMedias', 'erps']);
        $settings->makeVisible(['pix_info']);
        $settings->append('logo_url');

        return $this->sendResponse($settings, 'Configurações atualizadas.', 200);
    }

    private function settingsWithStoreDefaults(int $storeId, ?Setting $settings): Setting
    {
        $store = Store::query()
            ->with('people.city.state')
            ->find($storeId);

        $defaults = [
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
            'whatsapp_phone' => $store?->people?->phone ?? '',
            'email_notification' => $store?->people?->email ?? '',
            'status' => (string) ($store?->status ?? 1),
            'portal_url' => url('/'),
        ];

        $settings ??= new Setting(['store_id' => $storeId]);

        foreach ($defaults as $key => $value) {
            if (($settings->{$key} === null || $settings->{$key} === '') && $value !== null && $value !== '') {
                $settings->{$key} = $value;
            }
        }

        if (! $settings->relationLoaded('city') && $settings->city_id) {
            $settings->load('city.state');
        }

        $settings->makeVisible(['pix_info']);
        $settings->append('logo_url');

        return $settings;
    }
}
