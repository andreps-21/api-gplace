<div class="row">
    <div class="col-12">
        <div class="painel-header">
            <div class="row">
                <div class="col-md-4">
                    {!!Form::text('grid', 'Nome')
                    ->attrs(['maxlength' => '20'])->value(isset($data)? $data->grid:'')->required()
                    !!}
                </div>
                <div class="col-md-4">
                    {!!Form::text('description', 'Descrição')
                    ->attrs(['maxlength' => '40'])->value(isset($data)? $data->description:'')->required()
                    !!}
                </div>
                <div class="col-md-2">
                {!!Form::select('type', 'Tipo')->attrs(['class' => 'type'])->options(['text' => 'Texto', 'color' => 'Cor'])->value(isset($data)? $data->type : '')->required()!!}
                </div>
                <div class="col-md-2">
                    {!!Form::select('is_enabled', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])->value(isset($data)? $data->is_enabled : 1)->required()
                    !!}
                </div>
            </div>
        </div>
        <ul class="nav nav-tabs" style="margin-top:30px">
            <li class="nav-item">
                <a href="#home" style="background-color:white" data-toggle="tab" class="nav-link active">
                    <span>Variação</span>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div id="home" class="tab-pane fade show active">
                <div class="painel-body">
                        <div class="row">
                            <div class="table-responsive">
                                <table id="tabela" class="table table-dynamic">
                                    <tbody>
                                        @if(isset($data))
                                            @forelse ($data->variation as $item)
                                            <tr class="dynamic-form">
                                                <td>
                                                    <button class="btn-sm btn-danger" type="button" onclick="remove('{{ $item->id }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                                <td style="width: 15%;">
                                                    <div class="form-group">
                                                        {!!Form::text('code[]','Codigo')->readonly()->value($item->id ? $item->id:'')!!}
                                                    </div>
                                                </td>
                                                <td style="width: 25%;">
                                                    <div class="form-group">
                                                        {!!Form::text('abbreviation[]', "Sigla")->attrs(['maxlength' => '3'])->value($item->abbreviation)->required()!!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::text('var_description[]', 'Descrição')->attrs(['class' => 'text', 'maxlength' => '30'])->value($item->variation)->required()!!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::text('representation[]', 'Representação')->attrs(['class' => 'color','maxlength' => '30'])->value($item->representation)->required()!!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::select('var_is_enabled[]', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])->value($item->is_enabled)->required()
                                                        !!}
                                                    </div>
                                                </td>


                                            </tr>
                                            @empty

                                            <tr class="dynamic-form">
                                                <td>
                                                    <button class="btn-sm btn-danger btn-remove">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::text('abbreviation[]', 'Sigla')->attrs(['class' => 'text','maxlength' => '3'])->required()!!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::text('var_description[]', 'Descrição')->attrs(['maxlength' => '30'])->required()!!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::text('representation[]', 'Representação')->attrs(['class' => 'color','maxlength' => '30'])->required()!!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::select('var_is_enabled[]', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])->required()->value(1)
                                                        !!}
                                                    </div>
                                                </td>

                                            </tr>
                                            @endforelse
                                        @else
                                            <tr class="dynamic-form">
                                                <td>
                                                    <button class="btn-sm btn-danger btn-remove">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::text('abbreviation[]', 'Sigla')->attrs(['class' => 'text','maxlength' => '3'])->required()!!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::text('var_description[]', 'Descrição')->attrs(['maxlength' => '30'])->required()!!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::text('representation[]', 'Representação')->attrs(['class' => 'color','maxlength' => '30'])->required()!!}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {!!Form::select('var_is_enabled[]', 'Ativo', [ 1 => 'Sim', 0 => 'Não'])->required()->value(1)
                                                        !!}
                                                    </div>
                                                </td>

                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row increment mt-3">
                            <div class="col-12">
                                <button class="btn btn-success btn-add" type="button"><i class="fas fa-plus"></i>Adicionar Variação</button>
                            </div>
                        </div>
                </div>
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
    $(function() {

        $('.color').prop('type', "{{ isset($data->type) ? $data->type : 'text' }}");

    $('.type').change(function () {
        if($(this).val() == 'text'){
            $('.color').prop('type', "text");
            $('.color').val('');
        }else{
            $('.color').prop('type', "color");
        }

    });

})

function remove(id){
    $.ajax({
            url: getUrl() + '/api/v1/variation/'+id+'/delete',
            type: 'DELETE',
            dataType: 'json',
            success: function(res){
                if(res.status =='200')
                {
                    swal(
                    'Sucesso !',
                    res.mes,
                    'success'
                    ).then(function(){
                        window.location.reload();
                    })
                }else{
                    swal(
                    'Atenção !',
                    res.mes,
                    'info'
                    ).then(function(){
                        window.location.reload();
                    })
                }
            }
        });
}
</script>
@endpush
