<div class="row">

    <div class="col-md-9">
        {!!Form::text('name', 'Nome')
        ->attrs(['maxlength' => 30])
        ->required()
        !!}
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
