<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header" data-background-color>
                Informações Pessoais
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('nif', 'CNPJ/CPF')
                        ->attrs(['class' => 'cpf_cnpj','maxlength' => 14])
                        ->required()
                        ->readonly(isset($item))
                        !!}
                    </div>
                    <div class="col-md-5">
                        {!!Form::text('name', 'Razão social/Nome')
                        ->required()
                        ->attrs(['maxlength' => 50])!!}
                    </div>
                    <div class="col-md-4">
                        {!!Form::text('formal_name', 'Nome fantasia/Apelido')
                        ->required()
                        ->attrs(['maxlength' => 30])!!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('state_registration', 'Insc. estadual/RG')
                        ->attrs(['maxlength' => 25])
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('municipal_registration', 'Inscrição municipal')
                        ->attrs(['maxlength' => 30])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::text('email', 'Email')
                        ->type('email')
                        ->attrs(['maxlength' => 45])
                        ->required()
                        ->readonly(isset($item))
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('contact', 'Contato')
                        ->attrs(['maxlength' => 30])
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('contact_email', 'Contato Email')
                        ->type('email')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('contact_phone', 'Contato Tel.')
                        ->attrs(['class' => 'phone'])
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('status', 'Status', ['' => 'Selecione'] + \App\Models\Provider::opStatus())
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('type', 'Tipo', ['' => 'Selecione'] + \App\Models\Provider::types())
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::text('phone', 'Telefone')
                        ->attrs(['class' => 'phone', 'maxlength' => 15])
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::text('zip_code', 'CEP')
                        ->attrs(['class' => 'cep','maxlength' => 9])
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!Form::select('city_id', 'Cidade', (isset($item)) ? [$item->city_id => $item->city ] : [])
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-8">
                        {!!Form::text('street', 'Endereço')
                        ->attrs(['maxlength' => 60])
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!Form::text('district', 'Bairro')
                        ->attrs(['maxlength' => 30])
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::date('birth_date', 'Dt. Nasc/Abertura')
                        ->value((isset($item)) ? $item->birth_date : '')
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('profession_id', 'Profissão')
                        ->options($professions->prepend('Selecione',''), 'name')
                        ->attrs(['class' => 'select2'])
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('own_transport', 'Transporte próprio', [ 1 => 'Sim', 0 => 'Não'])
                        ->value(isset($item) ? $item->own_transport : 1)
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('own_equipment', 'Equipamento próprio', [ 1 => 'Sim', 0 => 'Não'])
                        ->value(isset($item) ? $item->own_equipment : 1)
                        ->required()
                        !!}
                    </div>
                    <div class="col-md-12">
                        {!!Form::textarea('notes', 'Observações')
                        ->attrs(['rows' => 4, 'maxlength' => 200])
                        !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header" data-background-color>
                Conta Bancária
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        {!!Form::select('bank_id', 'Banco')
                        ->options($banks->prepend('Selecione', ''), 'info')
                        ->attrs(['class' => 'select2'])
                        ->required(false)
                        !!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::select('account_type', 'Tipo',['' => 'Selecione'] + \App\Models\Provider::accountTypes())
                        ->required(false)
                        !!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::text('agency', 'Agência')
                        ->required(false)
                        ->attrs(['maxlength' => 13, 'class' => 'agency'])!!}
                    </div>
                    <div class="col-md-6">
                        {!!Form::text('account', 'Conta')
                        ->required(false)
                        ->attrs(['maxlength' => 18, 'class' => 'account'])!!}
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
