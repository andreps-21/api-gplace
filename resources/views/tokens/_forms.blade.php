<div class="row">

    <div class="col-md-8">
        {!!Form::text('description', 'Descrição')
        ->attrs(['maxlength' => 50])
        ->required()
        !!}
    </div>

    <div class="col-md-4">
        {!!Form::select('store_id', 'Loja')
        ->options($stores->prepend('Selecione', ''))
        ->required()
        ->attrs(['class' => 'select2'])
        !!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
