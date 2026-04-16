<div class="row">
    <div class="col-12">
        <div class="painel-header">
            <div class="row">
                <div class="col-md-3">
                    {!! Form::text('id_position', 'ID Posição')
                    ->attrs(['maxlength' => 3])
                    ->readonly(true)->required(true)
                    ->value(isset($instance) ? $instance : $item->id_position)
                    !!}
                </div>
                <div class="col-md-7">
                    {!!Form::text('position_name', 'Nome Posição')
                    ->attrs(['maxlength' => '60'])
                    ->required(true)
                    !!}
                </div>
                <div class="col-md-2">
                    {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
                    ->value(isset($item) ? $item->is_enabled : 1)
                    ->required(true)
                    !!}
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
