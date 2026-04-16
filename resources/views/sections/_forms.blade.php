<div class="row">
    <div class="tab-content col-3">
        <div id="home" class="tab-pane fade show active">
            <div class="painel-body">
                <div class="row d-block">
                    <img id="preview-image"
                        src="{{asset((isset($item) && $item->image != null) ? 'storage/' . $item->image : 'images/noimage.png')}}"
                        class="img-fluid" width="200" height="150" />
                    <a href="javascript:window.utilities.changeImage();" class="btn btn-primary"
                        style="max-height:50px">Adicionar Imagem</a>
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
            <div class="col-md-6">
                {!!Form::text('name', 'Nome')
                ->required()
                ->attrs(['maxlength' => 40])!!}
            </div>
            <div class="col-md-3">
                {!!Form::select('type', 'Tipo', ['' => 'Selecione'] + $types)
                ->value(isset($item) ? $item->type : (isset($data) ? "A" : "S"))
                ->required()
                ->readonly(!isset($item))
                !!}
            </div>
            <div class="col-md-3">
                {!!Form::text('order', 'Ordem Menu')
                ->attrs(['maxlength' => 10, 'oninput' => "this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');"])
                ->required()
                !!}
            </div>
            <div class="col-md-3">
                {!!Form::select('is_home', 'Home', [ 1 => 'Sim', 0 => 'Não'])
                ->required()
                !!}
            </div>
            <div class="col-md-3">
                {!!Form::text('order_home', 'Ordem Home')
                ->attrs(['maxlength' => 10, 'oninput' => "this.value = this.value.replace(/[^0-9]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');"])
                !!}
            </div>
            <div class="col-md-3">
                {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
                ->value(isset($item) ? $item->is_enabled : 1)
                ->required()
                !!}
            </div>
            <div class="col-md-3">
                {!!Form::select('parent_id', 'Antecessor', $sections)
                ->value($data)
                ->readOnly()
                !!}
            </div>
            <div class="col-md-12">
                {!!Form::text('descriptive', 'Descritivo')
                ->attrs(['maxlength' => 120])!!}
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
            </div>
        </div>
    </div>
</div>
