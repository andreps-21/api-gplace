<div class="row">
  {{-- <div class="col-md-2">
    {!! Form::text('code', 'Código da Imagem')
    ->required(true)
    !!}
  </div> --}}
  <div class="col-md-6">
    {!! Form::text('name', 'Nome')
    ->attrs(['maxlength' => 50])
    ->required(true)
    !!}
  </div>
  <div class="col-md-3">
    {!! Form::text('size_width', 'Largura')->type('number')
    ->required(true)
    !!}
  </div>
  <div class="col-md-3">
    {!! Form::text('size_height', 'Altura')->type('number')
    ->required(true)
    !!}
  </div>
  <div class="col-md-2">
    {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
    ->value(isset($item) ? $item->is_enabled : 1)
    ->required(true)
    !!}
  </div>
  <div class="col-md-4">
    {!!Form::select('type', 'Tipo')
    ->options(\App\Enums\TypeImage::types())
    ->value(isset($item) ? $item->position : [])
    ->attrs(['class' => 'select2'])
    ->required(true)
    !!}
  </div>
  <div class="col-md-6">
    {!!Form::select('interfacePositions[]', 'Posições')
    ->options($interfacePositions)
    ->multiple()
    ->value(isset($item) ? $item->interfacePositions->modelKeys() : [])
    ->attrs(['class' => 'multiselect'])
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
    $(".multiselect").bsMultiSelect({
        useChoicesDynamicStyling: true,
    });
</script>
@endpush
