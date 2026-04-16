<div class="row">

    <div class="col-md-3">
        <div class="col-12">
        <h4>Imagem</h4>
            <label class="text-danger font-weight-bold">Atenção: Inserir imagem no formato 600x200 px.</label>
            <br>
            <img id="preview-image"
                src="{{asset((isset($item) && $item->image!= null)?'storage/'.$item->image:'images/noimage.png')}}"
                class="img-fluid" width="250" height="150" />
            <br />
            <a href="javascript:window.utilities.changeImage();" class="btn btn-darker">Trocar Imagem</a>
            <input type="file" name="image" id="image"
                class="d-none form-control @if($errors->has('image')) is-invalid @endif" accept="image/*">
            @if($errors->has('image'))
            <div class="invalid-feedback">{{$errors->first('image')}}</div>
            @endif
        </div>
    </div>
    <div class="col-md-9">
        <div class="row">
            <div class="col-md-8">
                {!!Form::text('name', 'Nome')
                ->required()
                ->attrs(['maxlength' => 30])!!}
            </div>
            <div class="col-md-2">
                {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])
                ->value(isset($item) ? $item->is_enabled : 1)
                ->required()
                !!}
            </div>
            <div class="col-md-2">
                {!!Form::select('is_public', 'Publica?', [ 1 => 'Sim', 0 => 'Não'])
                ->value(isset($item) ? $item->is_public : 1)
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
