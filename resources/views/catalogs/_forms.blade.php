<div class="row">
    <div class="tab-content col-3">
        <div id="home" class="tab-pane fade show active">
            <div class="painel-body">
                <div class="row d-block">
                    <img id="preview-image"
                        src="{{asset((isset($item) && $item->image != null) ? 'storage/' . $item->image : 'images/noimage.png')}}"
                        class="img-fluid" width="250" height="150" />
                    <a href="javascript:window.utilities.changeImage();" class="btn btn-primary"
                        style="max-height:50px">Trocar Imagem</a>
                    <input type="file" name="image" id="image"
                        class="d-none form-control @if($errors->has('image')) is-invalid @endif" accept="image/*">
                    @if($errors->has('image'))
                    <div class="invalid-feedback">{{$errors->first('image')}}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="tab-content col-9">
        <div class="row">
            <div class="col-md-10">
                {!!Form::text('name', 'Nome')
                ->required()
                ->attrs(['maxlength' => 40])!!}
            </div>
            <div class="col-md-2">
                {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
                ->value(isset($item) ? $item->is_enabled : 1)
                ->required()
                !!}
            </div>
            <div class="col-md-12">
                {!!Form::text('url', 'Url do catálogo')
                ->required()
                ->attrs(['maxlength' => 100])!!}
            </div>
            <div class="col-md-12">
                {!!Form::text('subject', 'Assunto(email)')
                ->attrs(['maxlength' => 100])!!}
            </div>
            <div class="col-md-12">
                {!!Form::textarea('text_email', 'Texto E-mail')
                ->attrs(['class' => 'textarea'])->id('text_email')
               !!}
            </div>

        </div>
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
    $('#text_email').summernote({
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
