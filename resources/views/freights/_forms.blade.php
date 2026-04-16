<div class="row">
    <div class="col-md-6">
        {!!Form::text('name', 'Nome')
        ->attrs(['maxlength' => 30])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('city_id', 'Cidade', (isset($item)) ? [$item->city_id => $item->city ] : [])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('is_enabled', 'Ativo', [0 => 'Não', 1 => 'Sim'])
        ->required()
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::text('description', 'Descritivo')
        ->attrs(['maxlength' => 60])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('zip_code_start', 'CEP Início')
        ->attrs(['class' => 'cep'])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('zip_code_end', 'CEP Fim')
        ->attrs(['class' => 'cep'])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('free_shipping_sales', 'Frete Grátis/VL. Vendas($)')
        ->value(isset($item) ? floatToMoney($item->free_shipping_sales) : 0)
        ->attrs(['class' => 'money'])
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('value_freight_fix', 'Valor Frete Fixo($)')
        ->value(isset($item) ? floatToMoney($item->value_freight_fix) : 0)
        ->attrs(['class' => 'money'])
        !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('percentage', 'Percentual de Frete(%)')
        ->value(isset($item) ? floatToMoney($item->percentage) : 0)
        ->attrs(['class' => 'percent'])
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::textarea('notes', 'Observação')
        ->attrs(['maxlength' => 150])
        !!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>