<div class="row">
    <div class="col-md-3">
        {!!Form::text('nif', 'CPF/CNPJ')
        ->attrs(['maxlength' => 14,'class'=>'cpf_cnpj'])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('state_registration', 'Insc. Estadual/RG')
        ->attrs(['maxlength' => 25])
        ->required()
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('name', 'Razão Social/Nome')
        ->attrs(['maxlength' => 50])
        ->required()!!}
    </div>

    <div class="col-md-7">
        {!!Form::text('formal_name', 'Nome Fantasia/Apelido')
        ->attrs(['maxlength' => 30])
        ->required()
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('email', 'Email')
        ->type('email')
        ->attrs(['maxlength' => 45])
        ->required()!!}
    </div>
    <div class="col-md-4">
        {!!Form::text('phone', 'Telefone')
        ->attrs(['class' => 'phone', 'maxlength' => 15])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('zip_code', 'CEP')
        ->attrs(['maxlength' => 9])
        ->required()
        !!}
    </div>
    <div class="col-md-5">
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
        {!!Form::select('status', 'Status', [ 1 => 'Liberado', 0 => 'Interessado',2 => 'Bloqueado',3=>'Cancelado'])
        ->attrs(['maxlength' => 25])
        ->required()!!}
    </div>
    <div class="col-md-4">
        {!!Form::select('profession_id', 'Profissão')
        ->options($professions->prepend('Selecione',''),'name')
        ->required()
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::select('service_area_id', 'Àrea de Atendimento')
        ->options($areas->prepend('Selecione',''),'description')
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('product_id', 'Produtos')
        ->options($products->prepend('Selecione',''),'commercial_name')
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('own_equipment', 'Equipa. Proprio', [ 1 => 'Sim', 0 => 'Não'])
        ->value(isset($item) ? $item->own_equipment : 1)
        ->required()!!}
    </div>
    <div class="col-md-2">
        {!!Form::select('own_transport', 'Transp. Proprio', [ 1 => 'Sim', 0 => 'Não'])
        ->value(isset($item) ? $item->own_transport : 1)
        ->required()!!}
    </div>
    <div class="col-md-3">
        {!!Form::date('birth_date', 'Dt. Nasc/Abertura')
        ->attrs(['maxlength' => 30])
        ->required()!!}
    </div>
    <div class="col-md-3">
        {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
        ->value(isset($item) ? $item->is_enabled : 1)
        ->required()
        !!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
