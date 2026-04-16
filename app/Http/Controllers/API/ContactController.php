<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validator = $this->getValidationFactory()
            ->make(
                $request->all(),
                $this->rules($request)
            );

        if ($validator->fails()) {
            return $this->sendError('Erro de Validação.', $validator->errors()->toArray(), 422);
        }

        $settings = Setting::where('store_id', $request->get('store')['id'])->first();

        if (!isset($settings)) {
            return $this->sendError("Não á configuracão cadastrada para essa loja.", null, 403);
        }

        if (!$settings->email_notification) {
            return $this->sendError("Não foi configurado um email de notificação.", null, 403);
        }

        Mail::send(
            'mails.contact',
            [
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'body' => $request->get('message'),
                'nif' => $request->get('nif')
            ],
            function ($message) use ($settings) {
                $message->to($settings->email_notification, $settings->full_name)
                    ->subject('Nova mensagem do portal');
            }
        );

        return $this->sendResponse([], "E-mail enviado com sucesso.");
    }

    private function rules(Request $request, $primaryKey = null, bool $changeMessages = false)
    {
        $rules = [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string'],
            'message' => ['required', 'string'],
            'nif' => ['required', 'string']
        ];

        $messages = [];

        return !$changeMessages ? $rules : $messages;
    }
}
