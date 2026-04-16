<div class="row">
    <div class="col-md-3">
        {!!Form::text('nif', 'CPF/CNPJ')
        ->attrs(['class' => 'cpf_cnpj'])
        ->readonly(isset($item->nif))
        ->required()
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('formal_name', 'Nome Completo/Razão Social')
        ->attrs(['class' => 'formal_name','maxlength' => 50])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('name', 'Nome Social/Nome Fantasia')
        ->attrs(['class' => 'name','maxlength' => 30])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('state_registration', 'RG/ Insc. Estadual')
        ->attrs(['class' => 'state_registration','maxlength' => 25])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('city_registration', 'Inscrição Municipal')
        ->attrs(['class' => 'city_registration','maxlength' => 25])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('status', 'Status')
        ->options(\App\Models\Lead::status())
        ->attrs(['class' => 'select2'])
        ->required()
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('email', 'Email')
        ->type('email')
        ->attrs(['class' => 'email', 'maxlength' => 100])
        ->readonly(isset($item))
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('phone', 'Telefone')
        ->attrs(['class' => 'phone','maxlength' => 15])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('city_id', 'Cidade')
        ->options(isset($item) ? [$item->city_id => $item->city ] : [])
        ->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('zip_code', 'CEP')
        ->attrs(['class' => 'cep','maxlength' => 9])
        ->required()
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('address', 'Endereço')
        ->attrs(['class' => 'address', 'maxlength' => 60])
        ->required()
        !!}
    </div>

    <div class="col-md-3">
        {!!Form::text('district', 'Bairro')
        ->attrs(['class' => 'district' ,'maxlength' => 30])
        !!}
    </div>

    <div class="col-md-2">
        {!!Form::text('number', 'Número')
        ->attrs(['class' => 'number','maxlength' => 10])
        !!}
    </div>

    <div class="col-md-12">
        {!!Form::textarea('observation', 'Observação')
        ->attrs(['maxlength' => 150])
        !!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
