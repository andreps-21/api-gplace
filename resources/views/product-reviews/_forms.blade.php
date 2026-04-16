<div class="row">
    <div class="col-md-4">
        {!!Form::select('product_id', 'Produto')
        ->options($products->prepend('Selecione',''),'name')
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('note', 'Nota', [ 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
        ->value(isset($item) ? $item->note : 1)
        ->required()
        !!}
    </div>

    <div class="col-md-4">
        {!!Form::text('comment', 'Comentario')
        ->attrs(['maxlength' => 30])!!}
    </div>

</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
