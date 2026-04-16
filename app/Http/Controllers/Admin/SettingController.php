<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rules\CpfCnpj;
use App\Models\Setting;
use App\Models\SocialMedia;
use App\Models\Erp;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings_edit', ['only' => ['edit', 'update']]);
        $this->middleware('store');
    }

    public function edit()
    {
        $settings = Setting::where('store_id', session('store')['id'])
            ->with('city.state', 'socialMedias')
            ->first();

        $stamps = [];
        if ($settings) {
            if (!empty($settings->stamps)) {
                $stamps = collect(json_decode($settings->stamps));
            }

            $settings->makeVisible(['pix_info']);
        }

        $socialMedias = SocialMedia::where('is_enabled', true)->get(['id', 'description']);

        $erps = Erp::where('status', true)->get(['id', 'description']);

        return view('settings.edit', compact('settings', 'stamps',  'socialMedias', 'erps'));
    }

    public function update(Request $request)
    {
        $this->validate(
            $request,
            $this->rules($request, null)
        );


        try {

            $settings = Setting::where('store_id', session('store')['id'])->first();

            if (!$settings) {
                $settings = new Setting();
            }

            $inputs = $request->all();
            $inputs['store_id'] = session('store')['id'];

            $settings->fill($inputs);


            if ($request->hasFile('logo') && $request->file('logo')->isValid()) {

                $upload = $request->file('logo')->store('settings', 'public');

                if ($settings->logo) {
                    Storage::disk('public')->delete($settings->logo);
                }

                $settings->logo = $upload;
            }

            if ($request->hasFile('logo_footer') && $request->file('logo_footer')->isValid()) {

                $upload = $request->file('logo_footer')->store('settings', 'public');

                if ($settings->logo_footer) {
                    Storage::disk('public')->delete($settings->logo_footer);
                }

                $settings->logo_footer = $upload;
            }

            if ($request->hasFile('stamp_img') || (!empty($request->stamp_url) && array_filter($request->stamp_url, function ($a) {
                return $a !== null;
            }))) {

                $store_file = [];
                $files = $request->file('stamp_img');
                $urls = $request->stamp_url;
                $arquivos = json_decode($settings->stamps);

                foreach ($urls as $key => $url) {

                    if (isset($files[$key]) && $files[$key]->isValid()) {
                        $upload = $files[$key]->store('settings', 'public');
                    } else if (isset($arquivos[$key]->img)) {
                        $upload = $arquivos[$key]->img;
                    }


                    if ($settings->stamps) {
                        if (isset($arquivos[$key]->img) && $arquivos[$key]->img != $upload)
                            Storage::disk('public')->delete($arquivos[$key]->img);
                    }

                    if (isset($upload)) {
                        $store_file[] = [
                            'img' =>  $upload,
                            'url' =>  $url
                        ];
                        $upload = '';
                    }
                }

                if (!empty($store_file))
                    $settings->stamps = json_encode($store_file);
            }

            $arquivos = json_decode($settings->stamps);
            if (isset($request->remove_files)) {
                foreach ($request->remove_files as $key => $value) {
                    if (!empty($value)) {
                        Storage::disk('public')->delete($arquivos[$key]->img);
                        $arquivos[$key]->img = null;
                    }
                }
            }
            $settings->stamps = json_encode($arquivos);

            $settings->save();

            $socialMedias = [];
            if (!empty($request->social_media_id)) {
                foreach ($request->social_media_id as $index => $value) {
                    if ($value) {
                        $socialMedias[$value] = [
                            'url' => $request->social_media_url[$index] ?? null,
                            'user' => $request->social_media_user[$index] ?? null,
                            'password' => $request->social_media_password[$index] ?? null,
                            'token' => $request->social_media_token[$index] ?? null,
                        ];
                    }
                }
            }
            $settings->socialMedias()->sync($socialMedias);

            $erps = [];
            if (!empty($request->erp_id)) {
                foreach ($request->erp_id as $index => $value) {
                    if ($value) {
                        $erps[$value] = [
                            'url' => $request->erp_url[$index] ?? null,
                            'terminal' => $request->erp_terminal[$index] ?? null,
                            'id_emp' => $request->erp_id_emp[$index] ?? null,
                        ];
                    }
                }
            }

            $settings->erps()->sync($erps);


            return redirect()->route('settings.edit')
                ->withStatus('Registro atualizado com sucesso.');
        } catch (\Throwable $th) {
            return redirect()->route('settings.edit')
                ->withError('Registro não pode ser atualizado.' . $th);
        }
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'max:35'],
            'full_name' => ['required', 'max:50'],
            'nif' => ['required', new CpfCnpj],
            'city_id' => ['required'],
            'address' => ['required', 'max:50'],
            'number' => ['required', 'max:5'],
            'district' => ['nullable', 'max:35'],
            'maps' => ['nullable', 'max:150'],
            'contact' => ['nullable', 'max:30'],
            'zip_code' => ['required', 'max:9'],
            'email' => ['required', 'max:50'],
            'phone' => ['required', 'max:15'],
            'status' => ['required'],
            'logo' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:1000'],
            'logo_footer' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:1000'],
            'note' => ['nullable', 'max:200'],
            'instagram_url' => ['nullable', 'max:80'],
            'instagram_user' => ['nullable', 'max:40'],
            'instagram_password' => ['nullable', 'max:15'],
            'facebook_url' => ['nullable', 'max:80'],
            'facebook_user' => ['nullable', 'max:40'],
            'facebook_password' => ['nullable', 'max:15'],
            'youtube_url' => ['nullable', 'max:80'],
            'youtube_user' => ['nullable', 'max:40'],
            'youtube_password' => ['nullable', 'max:15'],
            'twitter_url' => ['nullable', 'max:80'],
            'twitter_user' => ['nullable', 'max:40'],
            'twitter_password' => ['nullable', 'max:15'],
            'pixels' => ['nullable', 'max:80'],
            'ads' => ['nullable', 'max:80'],
            'meta_tags' => ['nullable', 'max:200'],
            'footer' => ['nullable', 'max:200'],
            'payment_gateway' => ['nullable'],
            'payment_info.sandbox' => ['nullable', 'boolean'],
            'payment_info.email' => ['nullable', 'email'],
            'payment_info.token' => ['nullable', 'string'],
            'freight_gateway' => ['nullable'],
            'freight_info.sandbox' => ['nullable', 'boolean'],
            'freight_info.zip_code' => ['nullable', 'max:9', 'min:9'],
            'freight_info.token' => ['nullable', 'string'],
            'freight_info.public_key' => ['nullable', 'string'],
            'portal_url' => ['required', 'url'],
            'stamp_url' => ['nullable'],
            'stamp_img' => ['nullable'],
            'email_notification' => ['required', 'max:50'],
            'pix_gateway' => ['nullable'],
            'pix_info.sandbox' => ['nullable', 'boolean'],
            'pix_cert_crt' => ['nullable', 'file'],
            'pix_cert_key'  => ['nullable', 'file'],
            'pix_info.chave' => ['nullable', 'string'],
            'pix_info.client_id' => ['nullable', 'string'],
            'pix_info.client_secret' => ['nullable', 'string'],
            'integration_info.url' => ['nullable', 'string'],
            'integration_info.terminal' => ['nullable', 'string'],
            'integration_info.empId' => ['nullable', 'string'],
            'whatsapp_phone' => ['required', 'max:15'],
            'cookies' => ['nullable'],
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
