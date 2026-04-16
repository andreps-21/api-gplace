<div class="row">
    <div class="col-md-4">
        {!!Form::text('nif', 'CPF/CNPJ')
        ->attrs(['class' => 'cpf_cnpj'])
        ->required()
        ->readonly(isset($item))
        !!}
    </div>
    <div class="col-md-8">
        {!!Form::text('formal_name', 'Razão Social')
        ->required()
        ->attrs(['maxlength' => 60])!!}
    </div>
    <div class="col-md-6">
        {!!Form::text('name', 'Nome Fantasia')
        ->required()
        ->attrs(['maxlength' => 30])!!}
    </div>
    <div class="col-md-6">
        {!!Form::text('email', 'Email')->type('email')
        ->required()
        ->readonly(isset($item))
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('phone', 'Telefone')
        ->attrs(['class' => 'phone'])
        ->required()
        !!}
    </div>

    <div class="col-md-8">
        {!!Form::text('street', 'Endereço')
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('zip_code', 'CEP')
        ->attrs(['class' => 'cep'])
        ->required(false)
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('city_id', 'Cidade', (isset($item)) ? [$item->city_id => $item->city ] : [])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('status', 'Status', [null => 'Selecione...'] + \App\Models\Store::opStatus())
        ->value(isset($item) ? $item->status : 1)
        ->required()
        !!}
    </div>
    <div class="col-md-12">
        {!!Form::select('paymentMethods[]', 'Formas de Pagamento', $paymentMethods->pluck('description', 'id')->all())
        ->multiple()
        ->required()
        ->attrs(['class' => 'select2'])
        ->value(isset($item) && $item->paymentMethods ? $item->paymentMethods->pluck('id')->all() : null)
        !!}
    </div>
</div>

<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
