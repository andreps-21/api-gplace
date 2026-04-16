<div class="row">
    <div class="col-md-3">
        {!!Form::text('nif', 'CNPJ/CPF')
        ->attrs(['class' => 'cpf_cnpj'])
        ->required()
        ->readonly(isset($item))
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('formal_name', 'Razão Social/Nome')
        ->attrs(['maxlength' => 50])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('name', 'Nome Fantasia/Apelido')
        ->attrs(['maxlength' => 30])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('state_registration', 'Insc. Estadual/Produtor/RG')
        ->attrs(['maxlength' => 25])
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('email', 'Email')
        ->type('email')
        ->attrs(['maxlength' => 89])
        ->required()
        ->readonly(isset($item))
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
    <div class="col-md-7">
        {!!Form::text('street', 'Endereço')
        ->attrs(['maxlength' => 60])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('number', 'Número')
        ->attrs(['maxlength' => 10])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('district', 'Bairro')
        ->attrs(['maxlength' => 30])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('city_id', 'Cidade', (isset($item)) ? [$item->city_id => $item->city ] : [])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('type', 'Tipo', ['' => 'Selecione'] + \App\Models\Customer::types())
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('contact', 'Contato')
        ->attrs(['maxlength' => 30])
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('contact_email', 'Contato Email')
        ->type('email')
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('contact_phone', 'Contato Tel.')
        ->attrs(['class' => 'phone'])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('status', 'Status', ['' => 'Selecione'] + \App\Models\Customer::opStatus())
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!! Form::date('birth_date', 'Dt. Nasc/Abertura') !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('origin', 'Origem', ['' => 'Selecione'] + \App\Models\Customer::origins())
        ->required()!!}
    </div>
    <div class="col-md-12">
        {!!Form::textarea('notes', 'Observações')
        ->attrs(['rows' => 4, 'maxlength' => 200])
        !!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
