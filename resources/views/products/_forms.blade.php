@push('css')
  <style>
    span.tag.label.label-info [data-role="remove"]::before {
      content: "x";
      font-size: 10px;
      font-weight: 800;
      position: relative;
      top: -2px;
      padding: 0px 5px;
    }

    span.tag.label.label-info {
      border: 1px solid #d5c3c3;
      padding: 0 9px 0 9px;
      border-radius: 6px;
      background: #fff;
    }
  </style>
@endpush
<div class="row">
  <div class="col-md-12">
    <div class="row">
      <div class="col-md-6">
        <h4>Imagens</h4>
        <label class="text-danger font-weight-bold">Atenção: Inserir imagem no tamanho 360x460px</label>
        <div class="row">

          <div class="col-md-4">
            @if (isset($item) && count($item->images) > 0)
              <input type="hidden"
                name="oldimages[]"
                value={{ $item->images[0]->id }}>
              <img src="{{ asset('storage/' . $item->images[0]->name) }}"
                class="preview-image img-thumbnail" />
            @else
              <img src="{{ asset('images/noimage.png') }}"
                class="preview-image img-thumbnail" />
            @endif
            <a href="#"
              class="btn-image">Trocar</a>

            <input type="file"
              name="images[]"
              id="images"
              class="d-none form-control images @if ($errors->has('image')) is-invalid @endif">

              <a href="#"
                class="btn-remove-image text-danger ml-2">Remover</a>
            @if ($errors->has('image'))
              <div class="invalid-feedback">{{ $errors->first('image') }}</div>
            @endif
          </div>

          <div class="col-md-4">
            @if (isset($item) && count($item->images) > 1)
              <input type="hidden"
                name="oldimages[]"
                value={{ $item->images[1]->id }}>
              <img src="{{ asset('storage/' . $item->images[1]->name) }}"
                class="preview-image img-thumbnail" />
            @else
              <img src="{{ asset('images/noimage.png') }}"
                class="preview-image img-thumbnail" />
            @endif
            <a href="#"
              class="btn-image">Trocar</a>

            <input type="file"
              name="images[]"
              id="image"
              class="d-none form-control images @if ($errors->has('image')) is-invalid @endif">
              <a href="#"
                class="btn-remove-image text-danger ml-2">Remover</a>
            @if ($errors->has('image'))
              <div class="invalid-feedback">{{ $errors->first('image') }}</div>
            @endif
          </div>


          <div class="col-md-4">
            @if (isset($item) && count($item->images) > 2)
              <input type="hidden"
                name="oldimages[]"
                value={{ $item->images[2]->id }}>
              <img src="{{ asset('storage/' . $item->images[2]->name) }}"
                class="preview-image img-thumbnail" />
            @else
              <img src="{{ asset('images/noimage.png') }}"
                class="preview-image img-thumbnail" />
            @endif
            <a href="#"
              class="btn-image">Trocar</a>
            <input type="file"
              name="images[]"
              id="image"
              class="d-none form-control images @if ($errors->has('image')) is-invalid @endif">
              <a href="#"
                class="btn-remove-image text-danger ml-2">Remover</a>
            @if ($errors->has('image'))
              <div class="invalid-feedback">{{ $errors->first('image') }}</div>
            @endif
          </div>

        </div>
      </div>
      <div class="col-md-6">
        <div class="row">
          <div class="col-md-8">
            {!! Form::text('commercial_name', 'Nome Comercial')->attrs(['maxlength' => 60])->required()->readonly(isset($item) && $item->is_grid) !!}
          </div>
          <div class="col-md-4">
            {!! Form::select('is_grid', 'Usa Grade', [0 => 'Não', 1 => 'Sim'])->id('is_grid')->readOnly(isset($item) ? true : false) !!}
          </div>
          <div class="col-md-3">
            {!! Form::text('reference', 'Referência')->attrs(['maxlength' => 15])->readonly(isset($item))->required()->value(isset($reference) ? $reference : $item->reference)->id('reference') !!}
          </div>
          <div class="col-md-3">
            {!! Form::text('external_id', 'Cod. ERP')->attrs(['maxlength' => 20])->readonly(isset($item)) !!}
          </div>
          <div class="col-md-6">
            <option value="">Seção Principal</option>
            <select class="select2" name="section_id" class="form-control">
              <option value="">Selecione</option>
              @foreach ($sections as $section)
              <option value="{{ $section->id }}" @if(!$sinteticasSemAnaliticas->contains('id', $section->id) && !$section->parent_id != null) disabled @endif
                @if (isset($item) && $item->section_id == $section->id) selected @endif >
                @if($section->parent_id == null)
                  {{ $section->id." - ".$section->name }}
                @else
                  {{ ( $section->parent_id.".0".( $section->id - $section->parent_id ) )." - ".$section->name }}
                @endif
              </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-12">
            {!! Form::textarea('description_reference', 'Descrição Curta')->attrs(['maxlength' => 180])->required(isset($item) && $item->is_grid) !!}
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    {!! Form::select('origin', 'Origem', [1 => 'E-commerce', 0 => 'ERP']) !!}
  </div>
  <div class="col-md-3">
    {!! Form::select('type', 'Tipo', ['' => 'Selecione'] + [\App\Models\Product::PRODUCT => 'Produto'])->value(isset($item) ? $item->type : \App\Models\Product::PRODUCT)->required() !!}
  </div>
  <div class="col-md-2">
    {!! Form::select('um_id', 'Unidade Medida')->options($ums->prepend('Selecione', ''), 'initials')->required() !!}
  </div>
  <div class="col-md-2">
    {!! Form::text('model', 'Modelo')->required() !!}
  </div>
  <div class="col-md-2">
    {!! Form::select('brand_id', 'Marca')->options($brands->prepend('Selecione', ''), 'name')->attrs(['class' => 'select2'])->required() !!}
  </div>
  <div class="col-md-2">
    {!! Form::text('price', 'Preço')->value(isset($item) ? floatToMoney($item->price) : 0)->attrs(['class' => 'money'])->required() !!}
  </div>
  <div class="col-md-2">
    {!! Form::text('promotion_price', 'Preço Promocional')->value(isset($item) ? floatToMoney($item->promotion_price) : 0)->attrs(['class' => 'money']) !!}
  </div>

  <div class="col-md-2">
    {!! Form::text('discount', 'Desconto&nbsp;á&nbsp;Vista/Pix&nbsp;(%)')->value(isset($item) ? floatToMoney($item->discount) : 0)->attrs(['class' => 'percent']) !!}
  </div>
  <div class="col-md-2">
    {!! Form::text('weight', 'Peso (kg)')->value(isset($item) ? floatToMoney($item->weight) : 0)->attrs(['class' => 'money'])->required() !!}
  </div>
  <div class="col-md-2">
    {!! Form::text('cubic_weight', 'Peso Cubico (kg)')->value(isset($item) ? floatToMoney($item->cubic_weight) : 0)->attrs(['class' => 'money']) !!}
  </div>
  <div class="col-md-2">
    {!! Form::text('length', 'Comprimento(C)')->value(isset($item) ? floatToMoney($item->length) : 0)->attrs(['class' => 'money'])->required() !!}
  </div>
  <div class="col-md-2">
    {!! Form::text('width', 'Largura(LA)')->value(isset($item) ? floatToMoney($item->width) : 0)->attrs(['class' => 'money'])->required() !!}
  </div>
  <div class="col-md-2">
    {!! Form::text('height', 'Altura(A)')->value(isset($item) ? floatToMoney($item->height) : 0)->attrs(['class' => 'money'])->required() !!}
  </div>
  <div class="col-md-2">
    {!! Form::select('is_enabled', 'Ativo', [1 => 'Sim', 0 => 'Não'])->value(isset($item) ? $item->is_enabled : 1)->required() !!}
  </div>
  <div class="col-md-2">
  {!! Form::text('quantity', 'Qtd. Estoque')->value(isset($item) ? $item->quantity : 0)->required()->type('number') !!}
  </div>
  <div class="col-md-4">
    {!! Form::text('payment_condition', 'Cond. Pagto')->attrs(['maxlength' => 30]) !!}
  </div>

  <div class="col-md-12">
    {!! Form::text('tag', 'Tag')->attrs(['data-role' => 'tagsinput'])->multiple() !!}
  </div>
  <div class="col-md-12">
  <option value="">Seções</option>
       <select class="select2" name="section_id" class="form-control">
         <option value="">Selecione</option>
         @foreach ($sections as $section)
         <option value="{{ $section->id }}" @if(!$sinteticasSemAnaliticas->contains('id', $section->id) && !$section->parent_id != null) disabled @endif
           @if (isset($item) && $item->section_id == $section->id) selected @endif >
           @if($section->parent_id == null)
             {{ $section->id." - ".$section->name }}
           @else
             {{ ( $section->parent_id.".0".( $section->id - $section->parent_id ) )." - ".$section->name }}
           @endif
      </option>
          @endforeach
       </select>
    </div>
  <div class="col-md-12">
    {!! Form::select('paymentMethods[]', 'Formas de Pagamento', $paymentMethods->pluck('description', 'id')->all())->multiple()->required()->attrs(['class' => 'select2 rounded'])->value(isset($item) && $item->paymentMethods ? $item->paymentMethods->pluck('id')->all() : null) !!}
  </div>
  <div class="col-md-12">
    {!! Form::textarea('description', 'Descrição')->attrs(['class' => 'summernote'])->id('description') !!}
  </div>
  <div class="col-md-12">
    {!! Form::textarea('specification', 'Especificação')->attrs(['class' => 'summernote'])->id('specification') !!}
  </div>
  <div class="col-md-12">
    {!! Form::select('products[]', 'Produtos Relacionados', $products->pluck('commercial_name', 'id')->all())->multiple()->required(false)->attrs(['class' => 'select2 rounded products'])->value(isset($item) && $item->products ? $item->products->pluck('id')->all() : null) !!}
  </div>
  <div class="col-md-12">
    {!! Form::text('video', 'Vídeo')->attrs(['maxlength' => 50]) !!}
  </div>
</div>
@if (!isset($item))
  <div class="row"
    id="grids"
    style="{{ isset($item->is_grid) ? '' : 'display:none' }}">
    <div class="col-md-12">
      <div class="form-group"
        id="add_variacao">
        {!! Form::select('grid_id[]', 'Grades')->options($grids->pluck('grid', 'variation')->all())->attrs(['class' => 'select2'])->multiple()->id('grid') !!}
      </div>
    </div>

    <div class="col-md-12 variacoes"
      style="display:none">Variações
      <div class="tab-content">
        <div id="home"
          class="tab-pane fade show active">
          <div class="painel-body">
            <div class="row">
              <div class="table-responsive">
                <table id="variacoes"
                  class="table table-dynamic"
                  style="height:100px;">

                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
@endif
</div>
<div class="row">
  <div class="col-12">
    <button type="submit"
      class="btn btn-success float-right mt-4">Salvar</button>
  </div>
</div>

@push('js')
  <script>
    $(".products").select2({
      maximumSelectionLength: 10
    });

    $(document).ready(function() {

      $('#description').summernote({
        lang: "pt-BR",
        toolbar: [
          ['font', ['italic','underline', 'clear']],
          ['insert', ['link']],
          ['table', ['table']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    $('#specification').summernote({
        lang: "pt-BR",
        toolbar: [
          ['font', ['italic','underline', 'clear']],
          ['insert', ['link', 'picture']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

      handleSection();

      $('.variations').select2();

      $('#is_grid').change(function() {
        document.getElementById("reference").readOnly = $('#is_grid').val() == 1;
        document.getElementById("inp-description_reference").required = $('#is_grid').val() == 1;
        document.getElementById("grid").required = $('#is_grid').val() == 1;


        if ($('#is_grid').val() == 1) {
          $("#grids").show();
          $("#inp-description_reference, #grid")
            .siblings("label")
            .addClass("required");

        } else {
          $("#grids").hide();
          $("#inp-description_reference, #grid")
            .siblings("label")
            .removeClass("required");
        }
      });

      $('#add_variacao').change(function() {
        $(".variacoes").show();
        var variations = [];
        var grade = [];
        var grid = $('#grid').select2('data');
        grid.forEach(element => {
          variations.push(JSON.parse(element['id']));
          grade.push(element['text']);
        });
        lista(variations, grade);

      });
      $('#inp-section_id').on('change', handleSection)
    });

    function handleSection() {
      var value = $('#inp-section_id').val();

      $(".sections option").attr('disabled', false)

      if (value) {
        $(".sections option[value=" + value + "]").attr('disabled', true)
      }

      $(".sections").trigger('change')
    }


    function lista(dados, grade) {

      var html = '';
      html += '<tbody><tr class="dynamic-form">';

      for (let index = 0; index < dados.length; index++) {
        html += '<td style="width:25%">';
        html += '<div class="form-group">';
        html += '<label for=" ">' + grade[index] + '</label>';
        html += '<select class="js-example-basic-multiple" id="variation' + index + '" name="variation' + index +
          '[]" multiple="multiple" required>';
        dados[index].forEach(value => {
          html += '<option value="' + value.id + '">' + value.variation + '</option>';
        });
        html += '</select>';
        html += '</div>';
        html += '</td>';
      }
      html += '</tr></tbody>';

      $('#variacoes').html(html);
      $('.js-example-basic-multiple').select2();
      $("#addVar").hide();
    }
  </script>
@endpush
