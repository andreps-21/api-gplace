<div class="row">
    <div class="col-md-4">
        {!!Form::text('name', 'Nome')
        ->required()
        ->attrs(['maxlength' => 15])!!}
    </div>
    <div class="col-md-8">
        {!!Form::text('description', 'Descrição')
        ->required()
        ->attrs(['maxlength' => 40])!!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
