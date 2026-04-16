<div class="row">
    <div class="col-md-3">
        {!!Form::text('nif', 'CPF/CNPJ')
        ->attrs(['class' => 'cpf_cnpj'])
        ->required()
        ->readonly(isset($item))
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('formal_name', 'Razão Social/Nome')
        ->required()
        ->attrs(['maxlength' => 50])!!}
    </div>
    <div class="col-md-4">
        {!!Form::text('name', 'Nome Fantasia/Apelido')
        ->required()
        ->attrs(['maxlength' => 30])!!}
    </div>
    <div class="col-md-3">
        {!!Form::text('state_registration', 'Insc. Estadual/RG')
        ->attrs(['maxlength' => 25])
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('municipal_registration', 'Insc. Municipal')
        ->attrs(['maxlength' => 30])
        !!}
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
        {!!Form::text('phone', 'Telefone')
        ->attrs(['class' => 'phone', 'maxlength' => 15])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('status', 'Status', [null => 'Selecione...'] + \App\Models\Salesman::opStatus())
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
        {!!Form::text('zip_code', 'CEP')
        ->attrs(['class' => 'cep','maxlength' => 9])
        ->required()
        !!}
    </div>
    <div class="col-md-9">
        {!!Form::text('street', 'Endereço')
        ->attrs(['maxlength' => 60])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('city_id', 'Cidade', (isset($item)) ? [$item->city_id => $item->city ] : [])
        ->required()
        !!}
    </div>
    <div class="col-md-12">
        {!!Form::textarea('notes', 'Observação')
        ->attrs(['maxlength' => 200, 'rows' => 4])
        !!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
