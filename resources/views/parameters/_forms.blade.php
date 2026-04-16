<div class="row">
    <div class="col-md-8">
        {!! Form::text('name', 'Nome')->required()->attrs(['maxlength' => 30]) !!}
    </div>
    <div class="col-md-4">
        {!! Form::select('type', 'Tipo', [null => 'Selecione...'] + \App\Models\Parameter::opTypes())->required() !!}
    </div>
    <div class="col-md-12">
        {!! Form::textarea('value', 'Valor')->required()->attrs(['maxlength' => 250, 'rows' => 3]) !!}
    </div>
    <div class="col-md-12">
        {!! Form::textarea('description', 'Descrição')->required(false)->attrs(['maxlength' => 120, 'rows' => 2]) !!}
    </div>
</div>
<div class="row">
    <div class="col-12">
        <button type="submit" class="btn btn-success float-right mt-4">Salvar</button>
    </div>
</div>


@push('js')
    <script>
        $('#inp-type').on('change', function(){
            $('#inp-value').val('');
        })

        $('#inp-value').on('keypress', function(evt) {
            var type = $('#inp-type').val()

            if (type != 2) {
                return true;
            }

            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }

            return true;
        })
    </script>
@endpush
