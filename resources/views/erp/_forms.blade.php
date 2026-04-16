<div class="row">    
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-2">
                {!! Form::text('id', 'ID')->readonly() !!}
            </div>
            <div class="col-md-7">
                {!! Form::text('description', 'ERP/SISTEMA')->required()->attrs(['maxlength' => 40]) !!}
            </div>
            <div class="col-md-3">
                {!! Form::select('status', 'Ativo', [1 => 'Sim', 0 => 'Não'])->value(isset($item) ? $item->status : 1)->required() !!}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>
