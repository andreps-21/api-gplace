<div class="row">
    <div class="col-md-4">
        {!!Form::select('position', 'Posição', [null => 'Selecione...']+ \App\Models\Faq::positions())
        ->required()
        !!}
    </div>
    <div class="col-md-8">
        {!!Form::text('question', 'Pergunta')
        ->required()
        ->attrs(['maxlength' => 40])!!}
    </div>
    <div class="col-md-12">
        {!!Form::textarea('answer', 'Resposta')
        ->attrs(['class' => 'summernote'])->id('answer')
        ->required()
       !!}
    </div>
    <div class="col-md-10">
        {!!Form::text('url', 'Saiba Mais')
        ->type('url')
        ->attrs(['maxlength' => 60])!!}
    </div>
    <div class="col-md-2">
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

    $(document).ready(function() {
    $('#answer').summernote({
        lang: "pt-BR",
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
    });
    </script>
@endpush
