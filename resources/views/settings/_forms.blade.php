@push('css')
<style>
    .textStyle {
        padding-right: 2px;
        padding-left: 6px;
    }
    .nav-tabs .nav-item .nav-link.active {
        background-color: rgb(44, 13, 79); /* Cor de fundo quando selecionado */
        color: #fff; /* Cor do texto quando selecionado */
    }
</style>
@endpush

<div class="row">
    <div class="col-12 mb-2">
        <ul class="nav nav-tabs" id="myTabs">
            <li class="nav-item">
                <a class="nav-link active" id="perfil-tab" data-toggle="tab" href="#perfil" role="tab">Perfil Corporativo</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="termos-tab" data-toggle="tab" href="#termos" role="tab">Termos de Uso</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="policy-tab" data-toggle="tab" href="#politica" role="tab">Políticas de Privacidade</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="payment-tab" data-toggle="tab" href="#pagamento" role="tab">Pagamento/Frete</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="seo-tab" data-toggle="tab" href="#seo" role="tab">SEO/Analytics/API</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="integration-tab" data-toggle="tab" href="#integracao" role="tab">Integrações</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="social-tab" data-toggle="tab" href="#social" role="tab">Redes Sociais/Aplicativo</a>
            </li>
        </ul>
    </div>
    <div class="col-12">
        <div class="tab-content" id="myTabsContent">
            <div class="tab-pane fade show active" id="perfil" role="tabpanel" aria-labelledby="perfil-tab">
                <div class="row">
                    <div class="col-md-4 col-sm-12 mt-1">
                        <div class="card">
                            <div class="card-header bg-secondary">
                                Identidade Visual
                            </div>
                            <div class="card-body shadow-sm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-img label="Imagem Topo" name="logo" :value="isset($settings->logo)
                                            ? asset('storage/' . $settings->logo)
                                            : asset('images/noimage.png')" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-img label="Imagem Rodapé" name="logo_footer" :value="isset($settings->logo_footer)
                                            ? asset('storage/' . $settings->logo_footer)
                                            : asset('images/noimage.png')" />
                                    </div>                                                                      
                                </div>
                            </div>
                        </div>                                             
                    </div>

                    <div class="col-md-8 col-sm-12 mt-1">
                        <div class="card">
                            <div class="card-header bg-secondary">
                                Perfil Corporativo
                            </div>
                            <div class="card-body shadow-sm">
                                <div class="row">
                                    <div class="col-md-4">
                                        {!! Form::text('nif', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])->required() !!}
                                    </div>
                                    <div class="col-md-8">
                                        {!! Form::text('full_name', 'Razão Social')->attrs(['maxlength' => 50])->required() !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::text('name', 'Nome Fantasia')->attrs(['maxlength' => 35])->required() !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::text('email', 'Email')->attrs(['maxlength' => 50])->required() !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::text('phone', 'Telefone')->attrs(['class' => 'phone'])->required() !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::text('whatsapp_phone', 'WhatsApp')->attrs(['class' => 'phone'])->required() !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::select('status', 'Status', [null => 'Selecione...'] + \App\Enums\SettingsStatus::status())->attrs(['class' => 'select2'])->required() !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::text('zip_code', 'CEP')->attrs(['class' => 'cep'])->required() !!}
                                    </div>
                                    <div class="col-md-8">
                                        {!! Form::text('address', 'Endereço')->attrs(['maxlength' => 50])->required() !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('number', 'No.')->attrs(['maxlength' => 5])->required() !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::text('district', 'Bairro')->attrs(['maxlength' => 35]) !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::select(
                                            'city_id',
                                            'Cidade',
                                            isset($settings) ? [$settings->city_id => $settings->city->title . ' - ' . $settings->city->state->letter] : [],
                                        )->required() !!}
                                    </div>  
                                    <div class="col-md-6">
                                        {!! Form::text('maps', 'Local (Google maps)')->attrs(['maxlength' => 150]) !!}
                                    </div>  
                                    <div class="col-md-6">
                                        {!! Form::text('contact', 'Contato')->attrs(['maxlength' => 30]) !!}
                                    </div> 
                                    <div class="col-md-6">
                                        {!! Form::text('portal_url', 'URL Portal')->type('url')->required() !!}
                                    </div> 
                                    <div class="col-md-6">
                                        {!! Form::text('email_notification', 'Email Notificação')->type('email')->attrs(['maxlength' => 50])->required() !!}
                                    </div>        
                                </div>
                            </div>
                        </div>
                    </div>         

                    <div class="col-12 mt-1">
                        <div class="card">
                            <div class="card-header bg-secondary">
                                Footer/Rodapé
                            </div>
                            <div class="card-body shadow-sm">
                                <div class="row">
                                    <div class="col-md-6">
                                        {!! Form::textarea('footer', 'Rodapé')->attrs(['maxlength' => 200]) !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::textarea('cookies', 'Cookies')->attrs(['maxlength' => 200]) !!}
                                    </div>
                                </div>
                                {!! Form::textarea('note', 'Observação')->attrs(['maxlength' => 200]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mt-1">
                        <div class="card shadow">
                            <div class="card-header bg-secondary">
                                Selos
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-dynamic">
                                        <thead>
                                            <th style="width: 10px;"></th>
                                            <th style="min-width: 170px;">Imagem</th>
                                            <th style="min-width: 300px;">Selo</th>
                                        </thead>
                                        <tbody>
                                            @if (!empty($stamps) && $stamps->count() > 0)
                                                @foreach ($stamps as $key => $stamp)
                                                    <tr class="@if ($loop->first) dynamic-form @endif">
                                                        <td>
                                                            <button class="btn-sm btn-danger btn-remove" type="button"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </td>
                                                        <td class="inputFile"
                                                            @if ($stamp->img) style="vertical-align: top;" @endif>
                                                            @if ($stamp->img)
                                                                <a href="{{ asset('storage/' . $stamp->img) }}" target="_blank">Ver
                                                                    Imagem</a>
                                                            @endif
                                                            <input type="file" name="stamp_img[{{ $key }}]"
                                                                accept="image/*"
                                                                class="form-control @if ($errors->has('selo')) is-invalid @endif">
                                                        </td>
                                                        <td>
                                                            {!! Form::text('stamp_url[]', 'Selo')->type('url')->value(isset($stamp->url) && !empty($stamp->url) ? $stamp->url : '') !!}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="dynamic-form">
                                                    <td>
                                                        <button class="btn-sm btn-danger btn-remove" type="button"><i
                                                                class="fas fa-trash"></i></button>
                                                    </td>
                                                    <td class="inputFile">
                                                        <input type="file" name="stamp_img[]" accept="image/*"
                                                            class="form-control @if ($errors->has('selo')) is-invalid @endif">
                                                    </td>
                                                    <td>
                                                        {!! Form::text('stamp_url[]', 'Selo')->type('url')->value(isset($stamp->url) && !empty($stamp->url) ? $stamp->url : '') !!}
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row increment mt-4">
                                    <div class="col-12">
                                        <button class="btn btn-success btn-add" type="button"><i class="fas fa-plus"></i>Adicionar
                                            item</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="tab-pane fade" id="termos" role="tabpanel" aria-labelledby="termos-tab">
                <div class="row">
                    <div class="col-12 mt-1">
                        <div class="card">
                            <div class="card-header bg-secondary">
                            Termos de Uso
                            </div>
                            <div class="card-body shadow-sm">
                                <div class="col-12 mt-1">
                                    {!! Form::textarea('terms', 'Termos de Uso')->attrs(['class' => 'summernote']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="politica" role="tabpanel" aria-labelledby="policy-tab">
                <div class="row">
                    <div class="col-12 mt-1">
                        <div class="card">
                            <div class="card-header bg-secondary">
                            Políticas de Privacidade
                            </div>
                            <div class="card-body shadow-sm">
                                <div class="col-12 mt-1">
                                    {!! Form::textarea('privacy_policy', 'Política de Privacidade')->attrs(['class' => 'summernote']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pagamento" role="tabpanel" aria-labelledby="pagamento-tab">
                <div class="row">
                    <div class="col-12 mt-1">
                        <div class="card">
                            <div class="card-header bg-secondary">
                                Pagamento
                            </div>
                            <div class="card-body shadow-sm">
                                <div class="row">
                                    <div class="col-md-6">
                                        {!! Form::select(
                                            'payment_gateway',
                                            'Gateway de Pagamento',
                                            [null => 'Nennhum'] + \App\Enums\GatewayPayment::types(),
                                        )->attrs(['class' => 'select2']) !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::select('payment_info[sandbox]', 'Sanbox', [1 => 'Sim', 0 => 'Não'])->value(
                                            isset($settings) && $settings->payment_info && array_key_exists('sandbox', $settings->payment_info)
                                                ? $settings->payment_info['sandbox']
                                                : null,
                                        ) !!}
                                    </div>
                                </div>
                                <div class="row pagseguro">
                                    <div class="col-md-6">
                                        {!! Form::text('payment_info[email]', 'Email')->type('email')->attrs(['maxlength' => 89])->value(
                                                isset($settings) && $settings->payment_info && array_key_exists('email', $settings->payment_info)
                                                    ? $settings->payment_info['email']
                                                    : null,
                                            ) !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::text('payment_info[token]', 'Token')->value(
                                            isset($settings) && $settings->payment_info && array_key_exists('token', $settings->payment_info)
                                                ? $settings->payment_info['token']
                                                : null,
                                        ) !!}
                                    </div>
                                    <div class="col-md-12">
                                        {!! Form::textarea('payment_info[public_key]', 'Chave Pública')->value(
                                                isset($settings) && $settings->payment_info && array_key_exists('public_key', $settings->payment_info)
                                                    ? $settings->payment_info['public_key']
                                                    : null,
                                            )->attrs(['rows' => 4]) !!}
                                    </div>
                                </div>
                                <div class="row sicredi">
                                    <div class="col-md-6">
                                        {!! Form::text('payment_info[username]', 'Código do beneficiário + Código da Cooperativa')->attrs(['maxlength' => 60])->value(
                                                isset($settings) && $settings->payment_info && array_key_exists('username', $settings->payment_info)
                                                    ? $settings->payment_info['username']
                                                    : null,
                                            ) !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::text('payment_info[password]', 'Código de Acesso gerado no Internet Banking')->attrs(['maxlength' => 60])->value(
                                                isset($settings) && $settings->payment_info && array_key_exists('password', $settings->payment_info)
                                                    ? $settings->payment_info['password']
                                                    : null,
                                            ) !!}
                                    </div>
                                    <div class="col-md-12">
                                        {!! Form::text('payment_info[token_sicredi]', 'Access token gerado no portal do desenvolvedor')->value(
                                            isset($settings) && $settings->payment_info && array_key_exists('token_sicredi', $settings->payment_info)
                                                ? $settings->payment_info['token_sicredi']
                                                : null,
                                        ) !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::text('payment_info[cooperative]', 'Código da Cooperativa')->value(
                                            isset($settings) && $settings->payment_info && array_key_exists('cooperative', $settings->payment_info)
                                                ? $settings->payment_info['cooperative']
                                                : null,
                                        ) !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::text('payment_info[agency]', 'Código da Agência')->value(
                                            isset($settings) && $settings->payment_info && array_key_exists('agency', $settings->payment_info)
                                                ? $settings->payment_info['agency']
                                                : null,
                                        ) !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::text('payment_info[covenant]', 'Código do Convênio de Cobrança')->value(
                                            isset($settings) && $settings->payment_info && array_key_exists('covenant', $settings->payment_info)
                                                ? $settings->payment_info['covenant']
                                                : null,
                                        ) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-1">
                        <div class="card">
                            <div class="card-header bg-secondary">
                                Frete
                            </div>
                            <div class="card-body shadow-sm">
                                <div class="row">
                                    <div class="col-md-2">
                                        {!! Form::select(
                                            'freight_gateway',
                                            'Gateway de Pagamento',
                                            [null => 'Nenhum'] + \App\Enums\FreightType::types(),
                                        )->attrs(['class' => 'select2']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::select('freight_info[sandbox]', 'Sanbox', [1 => 'Sim', 0 => 'Não'])->value(
                                            isset($settings) && $settings->freight_info ? $settings->freight_info['sandbox'] : null,
                                        ) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('freight_info[zip_code]', 'CEP Origem')->value(isset($settings) && $settings->freight_info ? $settings->freight_info['zip_code'] : null)->attrs(['class' => 'cep']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        {!! Form::text('freight_info[client_id]', 'Client Id')->value(
                                            isset($settings) && $settings->freight_info && array_key_exists('client_id', $settings->freight_info)
                                                ? $settings->freight_info['client_id']
                                                : null,
                                        ) !!}
                                    </div>
                                    <div class="col-md-4">
                                        {!! Form::text('freight_info[client_secret]', 'Client Secret')->value(
                                            isset($settings) && $settings->freight_info && array_key_exists('client_secret', $settings->freight_info)
                                                ? $settings->freight_info['client_secret']
                                                : null,
                                        ) !!}
                                    </div>
                                    <div class="col-md-12">
                                        {!! Form::textarea('freight_info[token]', 'Token')->value(
                                            isset($settings) && $settings->freight_info ? $settings->freight_info['token'] : null,
                                        ) !!}
                                        <input type="hidden" name="freight_info[refresh_token]"
                                            value="{{ isset($settings) && $settings->freight_info && array_key_exists('refresh_token', $settings->freight_info) ? $settings->freight_info['refresh_token'] : null }}">
                                        <input type="hidden" name="freight_info[token_expiration_date]"
                                            value="{{ isset($settings) && $settings->freight_info && array_key_exists('token_expiration_date', $settings->freight_info) ? $settings->freight_info['token_expiration_date'] : null }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                <div class="row">
                    <div class="col-12 mt-1">
                        <div class="card">
                            <div class="card-header bg-secondary">
                                SEO
                            </div>
                            <div class="card-body shadow-sm">
                                <div class="row">
                                    <div class="col-md-6">
                                        {!! Form::textarea('pixels', 'Pixels')->attrs(['maxlength' => 80]) !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::textarea('ads', 'Google Analytics')->attrs(['maxlength' => 350]) !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::textarea('meta_tags', 'Meta tags')->attrs(['maxlength' => 200]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="integracao" role="tabpanel" aria-labelledby="integracao-tab">
                <div class="row">                   

                    <div class="col-md-12 mt-1">
                        <div class="card shadow">
                            <div class="card-header bg-secondary">
                                ERPS
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-dynamic">
                                        <thead>
                                            <th style="width: 10px;"></th>
                                            <th style="min-width: 170px;">ERP</th>
                                            <th style="min-width: 355px;" >URL</th>
                                            <th style="min-width: 355px;">Terminal</th>
                                            <th style="min-width: 60px;" >EMP ID</th>
                                        </thead>
                                        <tbody>
                                            @if (isset($settings) && $settings->erps->count() > 0)
                                                @foreach ($settings->erps as $erp)
                                                    <tr class="@if ($loop->first) dynamic-form @endif">
                                                        <td>
                                                            <button class="btn btn-danger btn-remove" type="button"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </td>
                                                        <td>
                                                            {!! Form::select('erp_id[]')->options($erps->prepend('Selecione', ''), 'description')->value($erp->id) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('erp_url[]')->value($erp->pivot->url)->attrs(['class' => 'ignore','maxlength' => 60]) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('erp_terminal[]')->value($erp->pivot->terminal)->attrs(['class' => 'ignore','maxlength' => 40]) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('erp_id_emp[]')->value($erp->pivot->id_emp)->attrs(['class' => 'ignore','maxlength' => 20]) !!}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="dynamic-form">
                                                    <td>
                                                        <button class="btn btn-danger btn-remove" type="button"><i
                                                                class="fas fa-trash"></i></button>
                                                    </td>
                                                    <td>
                                                        {!! Form::select('erp_id[]')->options($erps->prepend('Selecione', ''), 'description') !!}
                                                    </td>
                                                    <td>
                                                        {!! Form::text('erp_url[]') !!}
                                                    </td>
                                                    <td>
                                                        {!! Form::text('erp_terminal[]') !!}
                                                    </td>
                                                    <td>
                                                        {!! Form::text('erp_id_emp[]') !!}
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row increment mt-4">
                                    <div class="col-12">
                                        <button class="btn btn-success btn-add" type="button"><i class="fas fa-plus"></i>Adicionar
                                            item</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                <div class="row">
                    <div class="col-12 mt-1">
                        <div class="card">
                            <div class="card-header bg-secondary">
                                Aplicativo
                            </div>
                            <div class="card-body shadow-sm">
                                <div class="row">
                                    <div class="col-md-4 textStyle">
                                        {!! Form::text('android_ver', 'Versão Android')->attrs(['maxlength' => 20]) !!}
                                    </div>
                                    <div class="col-md-8 textStyle">
                                        {!! Form::text('android_url_store', 'URL Play Store')->attrs(['maxlength' => 80]) !!}
                                    </div>
                                    <div class="col-md-4 textStyle">
                                        {!! Form::text('apple_ver', 'Versão IOS')->attrs(['maxlength' => 20]) !!}
                                    </div>
                                    <div class="col-md-8 textStyle">
                                        {!! Form::text('apple_url_store', 'URL Apple Store')->attrs(['maxlength' => 80]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 mt-1">
                        <div class="card shadow">
                            <div class="card-header bg-secondary">
                                Redes Sociais
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-dynamic">
                                        <thead>
                                            <th style="width: 10px;"></th>
                                            <th style="min-width: 170px;">Rede Social</th>
                                            <th style="min-width: 300px;">Nome/URL</th>
                                            <th style="min-width: 200px;">Usuário</th>
                                            <th style="min-width: 200px;">Senha</th>
                                            <th style="min-width: 500px;">Token</th>
                                        </thead>
                                        <tbody>
                                            @if (isset($settings) && $settings->socialMedias->count() > 0)
                                                @foreach ($settings->socialMedias as $socialMedia)
                                                    <tr class="@if ($loop->first) dynamic-form @endif">
                                                        <td>
                                                            <button class="btn btn-danger btn-remove" type="button"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </td>
                                                        <td>
                                                            {!! Form::select('social_media_id[]')->options($socialMedias->prepend('Selecione', ''), 'description')->value($socialMedia->id) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('social_media_url[]')->value($socialMedia->pivot->url) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('social_media_user[]')->value($socialMedia->pivot->user)->attrs(['class' => 'ignore']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('social_media_password[]')->value($socialMedia->pivot->password)->attrs(['class' => 'ignore']) !!}
                                                        </td>
                                                        <td>
                                                            {!! Form::text('social_media_token[]')->value($socialMedia->pivot->token)->attrs(['class' => 'ignore']) !!}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="dynamic-form">
                                                    <td>
                                                        <button class="btn btn-danger btn-remove" type="button"><i
                                                                class="fas fa-trash"></i></button>
                                                    </td>
                                                    <td>
                                                        {!! Form::select('social_media_id[]')->options($socialMedias->prepend('Selecione', ''), 'description') !!}
                                                    </td>
                                                    <td>
                                                        {!! Form::text('social_media_url[]') !!}
                                                    </td>
                                                    <td>
                                                        {!! Form::text('social_media_user[]')->attrs(['class' => 'ignore']) !!}
                                                    </td>
                                                    <td>
                                                        {!! Form::text('social_media_password[]')->attrs(['class' => 'ignore']) !!}
                                                    </td>
                                                    <td>
                                                        {!! Form::text('social_media_token[]')->attrs(['class' => 'ignore']) !!}
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row increment mt-4">
                                    <div class="col-12">
                                        <button class="btn btn-success btn-add" type="button"><i class="fas fa-plus"></i>Adicionar
                                            item</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>



<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>

@push('js')
    <script>
        $(document).ready(function() {
            handlePayment();

            $('.btn-remove-image-second').on('click', function() {
                $(this).closest('div').parent().closest('div').find('.remove_files').val(1);
            });

            $(document).ready(function() {
                $('.summernote').summernote({
                    lang: "pt-BR",
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            });

            $('#inp-payment_gateway').on('change', handlePayment)
        })

        function handlePayment() {
            var value = $('#inp-payment_gateway').val()
            $('.pagseguro').addClass('d-none')
            $('.sicredi').addClass('d-none')

            if (value == 1) {
                $('.pagseguro').removeClass('d-none')
                $('.sicredi').find('input, select').val(null)
                $('.sicredi').find('textarea').text("")
            } else if (value == 2) {
                $('.sicredi').removeClass('d-none')
                $('.pagseguro').find('input, select').val(null)
                $('.pagseguro').find('textarea').text("")
            }
        }
    </script>
@endpush
