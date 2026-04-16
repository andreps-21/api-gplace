<div class="row">
    <div class="col-md-4">
        {!! Form::text('name', 'Nome')->required()->attrs(['maxlength' => 15]) !!}
    </div>
    <div class="col-md-8">
        {!! Form::text('description', 'Descrição')->attrs(['maxlength' => 40]) !!}
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="inp-start_at" class="required">Dt. Início</label>
            <input type="datetime-local" class="form-control" name="start_at" id="inp-start_at"
                value="{{ old('start_at', isset($item) ? str_replace(' ', 'T', $item->start_at) : null) }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="inp-end_at" class="required">Dt. Fim</label>
            <input type="datetime-local" class="form-control" name="end_at" id="inp-end_at"
                value="{{ old('end_at', isset($item) ? str_replace(' ', 'T', $item->end_at) : null) }}" required>
        </div>
    </div>
    <div class="col-md-4">
        {!! Form::select('sponsor', 'Patrocinador', ['' => 'Selecione'] + \App\Models\Coupon::sponsors())
         !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('apply', 'Aplica', ['' => 'Selecione'] + \App\Models\Coupon::applies())
        ->required() !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('business_unit_id', 'Unidade')
        ->options($businessUnits->prepend('Todas', ''))
    !!}
    </div>
    <div class="col-md-4">
        {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
        ->value(isset($item) ? $item->is_enabled : 1)
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('discount', 'Vl. Desconto')
        ->value(isset($item) ? floatToMoney($item->discount) : 0)
        ->attrs(['class' => 'money'])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('min_order', 'Vl. Min. Pedido')
        ->value(isset($item) ? floatToMoney($item->min_order) : 0)
        ->attrs(['class' => 'money'])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!! Form::text('quantity', 'Qtd. Max.')
        ->type('number')
        ->min(1)
        ->required()
        !!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>

