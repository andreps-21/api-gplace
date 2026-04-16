<div class="row">

    <div class="col-3">
        <div class="col-12">
            <label for="image">Ícone</label>
            <br>
            <img id="preview-image"
                src="{{asset((isset($item) && $item->icon!= null)?'storage/'.$item->icon:'images/noimage.png')}}"
                class="img-fluid" width="250" height="150" />
            <br />
            <a href="javascript:window.utilities.changeImage();" class="btn btn-darker">Trocar Imagem</a>
            <input type="file" name="icon" id="image"
                class="d-none form-control @if($errors->has('image')) is-invalid @endif" accept="image/*">
            @if($errors->has('image'))
            <div class="invalid-feedback">{{$errors->first('image')}}</div>
            @endif
        </div>
    </div>



    <div class="col-md-9">
        <div class="row">
            <div class="col-md-3">
                {!!Form::text('code', 'Código')
                ->required()
                ->attrs(['maxlength' => 30])!!}
            </div>
            <div class="col-md-6">
                {!!Form::text('description', 'Descrição')
                ->required()
                ->attrs(['maxlength' => 30])!!}
            </div>
            <div class="col-md-3">
                {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
                ->value(isset($item) ? $item->is_enabled : 1)
                ->required()
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
