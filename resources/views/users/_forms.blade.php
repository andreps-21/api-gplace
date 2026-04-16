<div class="row">
    <div class="col-md-4">
        {!!Form::text('nif', 'CPF/CNPJ')
        ->attrs(['class' => 'cpf_cnpj'])
        ->required()
        ->readonly(isset($item))
        !!}
    </div>
    <div class="col-md-8">
        {!!Form::text('name', 'Nome')
        ->required()
        ->attrs(['maxlength' => 70])!!}
    </div>
    <div class="col-md-6">
        {!!Form::text('email', 'Email')->type('email')
        ->required()
        ->readonly(isset($item))
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('phone', 'Telefone')
        ->attrs(['class' => 'phone'])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
        ->value(isset($item) ? $item->is_enabled : 1)
        ->required()
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::select('city_id', 'Cidade', (isset($item)) ? [$item->city_id => $item->city ] : [])
        ->required()
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::select('role', 'Atribuição',['' => 'Selecione'] + $roles->pluck('name_description', 'id')->all())
        ->value(isset($item) ? $item->roles->pluck('id')->all() : [])
        ->required()!!}
    </div>
    @if(!isset($item))
    <div class="col-md-6">
        {!!Form::text('password', 'Senha')->type('password')
        ->attrs(['minlength' => 8])
        ->required()
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::text('password_confirmation', 'Confirmar Senha')->type('password')
        ->attrs(['minlength' => 8])
        ->required()
        !!}
    </div>
    @endif
    <div class="col-md-12">
        {!!Form::select('stores[]', 'Loja', $stores->pluck('name', 'id')->all())
        ->multiple()
        ->required(false)
        ->attrs(['class' => 'select2'])
        ->value(isset($item) && $item->stores ? $item->stores->pluck('id')->all() : null)
        !!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
