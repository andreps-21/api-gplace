<div class="row">
    <div class="tab-content col-3">
        <div id="home" class="tab-pane fade show active">
            <div class="painel-body">
                <div class="row d-block">
                    <img id="preview-image"
                        src="{{asset((isset($item) && $item->filename != null) ? 'storage/' . $item->filename : 'images/noimage.png')}}"
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
            <div class="col-md-8">
                {!!Form::text('name', 'Nome')
                ->required()
                ->attrs(['maxlength' => 100])!!}
            </div>
            <div class="col-md-4">
                {!!Form::text('sequence', 'Sequência')
                ->type('number')
                ->min(1)
                ->max(3)
                ->required()
                !!}
            </div>
            <div class="col-md-4">
                {!!Form::select('type', 'Tipo de Mídia', [null => 'Selecione...']+ \App\Models\Banner::types())
                ->required()
                !!}
            </div>
            <div class="col-md-4">
                {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
                ->value(isset($item) ? $item->is_enabled : 1)
                ->required()
                !!}
            </div>
            <div class="col-md-12">
                {!! Form::select('sizeImages[]', 'Posições')
                ->options($sizeImages, 'name', 'code')
                ->multiple()
                ->attrs(['class' => 'multiselect'])
                ->value(isset($item) ? $item->sizeImages->pluck('code')->all() : [])
                ->required() !!}
            </div>
            <div class="col-md-12">
                {!!Form::textarea('url', 'URL/Script')
                ->attrs(['maxlength' => '250', 'cols' => 2, 'class' => 'url'])
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

@include('banners._modal')

@push('js')
<script>
    $(document).ready(function() {
        $('.type').on('change', function() {
            if ($(this).val() == 1) {
                $(".url").removeAttr('required');
                $("label[for='inp-url']").removeClass('required');
            }
            if ($(this).val() == 2) {
                $(".url").attr('required', true);
                $("label[for='inp-url']").addClass('required');
            }
        });
    });
    $(".multiselect").bsMultiSelect({
        useChoicesDynamicStyling: true,
    });
</script>
@endpush
