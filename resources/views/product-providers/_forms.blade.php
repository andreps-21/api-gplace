<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="product_id">Serviço</label>
            <select name="product_id" id="inp-product_id" class="form-control select2  @if($errors->has('product_id')) is-invalid @endif" required>
                <option value="">Selecione</option>
                @foreach ($products as $product)
                <option value="{{ $product->id }}" data-um="{{ $product->initials }}"
                    @if(isset($item)  && $item->product_id == $product->id) selected @endif>
                    {{ $product->commercial_name }}
                </option>
                @endforeach
            </select>
            @if($errors->has('product_id'))
            <div class="invalid-feedback">{{$errors->first('product_id')}}</div>
            @endif
        </div>
    </div>
    <div class="col-md-2">
        {!!Form::text('um', 'Unid. Medida')
        ->attrs(['maxlength' => 10])
        ->readonly()
        !!}
    </div>
    <div class="col-md-6">
        {!!Form::select('provider_id', 'Fornecedor')
        ->options($providers->prepend('Selecione',''))
        ->attrs(['class' => 'select2'])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('price', 'Vl. Serviço')
        ->attrs(['class' => 'money'])
        ->value(isset($item) ? floatToMoney($item->price) : 0)
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('vl_km', 'Vl. por km desl.')
        ->attrs(['class' => 'money'])
        ->value(isset($item) ? floatToMoney($item->vl_km) : 0)
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('vl_transfer', 'Repasse (%)')
        ->attrs(['class' => 'percent'])
        ->value(isset($item) ? floatToMoney($item->vl_transfer) : 0)
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


@push('js')
<script>
    $('#inp-product_id').on('change', function(){
        var um = $(this).find(':selected').data('um')
        $('#inp-um').val(um)
    })
</script>
@endpush
