<div class="row">
    <div class="col-md-3">
        {!!Form::text('nif', 'CPF/CNPJ')
        ->attrs(['class' => 'cpf_cnpj'])
        ->required()
        ->readonly(isset($item))
        !!}
    </div>
    <div class="col-md-5">
        {!!Form::text('formal_name', 'Razão Social')
        ->required()
        ->attrs(['maxlength' => 60])!!}
    </div>
    <div class="col-md-4">
        {!!Form::text('name', 'Nome Fantasia')
        ->required()
        ->attrs(['maxlength' => 30])!!}
    </div>
    <div class="col-md-4">
        {!!Form::text('email', 'Email')->type('email')
        ->required()
        ->readonly(isset($item))
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('phone', 'Telefone')
        ->attrs(['class' => 'phone'])
        ->required()
        !!}
    </div>
    <div class="col-md-4">
        {!!Form::text('cellphone', 'Celular')
        ->attrs(['class' => 'phone'])
        ->required(false)
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('zip_code', 'CEP')
        ->attrs(['class' => 'cep'])
        ->required(false)
        !!}
    </div>
    <div class="col-md-9">
        {!!Form::text('street', 'Endereço')
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('city_id', 'Cidade', (isset($item)) ? [$item->city_id => $item->city ] : [])
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('contact', 'Contato')
        ->required(false)
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::text('contact_phone', 'Tel. do contato')
        ->attrs(['class' => 'phone'])
        ->required(false)
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::select('status', 'Status', [null => 'Selecione...'] + \App\Models\Tenant::opStatus())
        ->value(isset($item) ? $item->status : 1)
        ->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('signature', 'Assinatura', [null => 'Selecione...'] + \App\Models\Tenant::opSignatures())
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::date('dt_accession', 'Dt. Adesão')
        ->value(isset($item) ? $item->dt_accession->format('Y-m-d') : null)
        ->required()
        !!}
    </div>
    <div class="col-md-3">
        {!!Form::date('due_date', 'Dt. Vigência Assinatura')
        ->value(isset($item) ? $item->due_date->format('Y-m-d') : null)
        ->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::select('due_day', 'Dia vencimento', [null => 'Selecione...'] + \App\Models\Tenant::opDueDays())
        ->required()
        !!}
    </div>
    <div class="col-md-2">
        {!!Form::text('value', 'Valor')
        ->attrs(['class' => 'money'])
        ->value(isset($item) ? money($item->value) : null)
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
        $('body').on('change', '.size-images', function(){
            $(this).closest('td').next().next().find('select').val(1)
        })

        $("label[for='inp-enableds[]'].required").css("visibility", "hidden");
        $("label[for='inp-sizeImages[]'].required").css("visibility", "hidden");

        $(".btn-add-item").on("click", function() {
            var $table = $(this).closest(".row").prev().find(".table-dynamic");

            var hasEmpty = false;
            $table.find("input, select").each(function() {
                if (
                    ($(this).val() == "" || $(this).val() == null) &&
                    $(this).attr("type") != "hidden" &&
                    $(this).attr("type") != "file" &&
                    !$(this).hasClass("ignore")
                ) {
                    hasEmpty = true;
                }
            });

            if (hasEmpty) {
                Swal.fire(
                    "Atenção",
                    "Preencha todos os campos antes de adicionar novos.",
                    "warning"
                );
                return;
            }

            $("tbody select.select2").select2("destroy");
            var $tr = $table.find(".dynamic-form").first();
            var $clone = $tr.clone();
            $clone.show();
            $clone.find("input,select").not('.ignore').val("");
            $clone.find('.created-at').prop('readonly', false)
            $clone.find('.created-at').val(new Date().toISOString().split('T')[0])

            var optionSelecteds = $('.size-images :selected').map(function (idx, ele) {
                return $(ele).val();
            }).get();

            $clone.find('.size-images option').each(function(){
               $(this).prop('disabled', optionSelecteds.includes($(this).val()))
            });

            $table.append($clone);
            setTimeout(function() {
                $("tbody select.select2").select2({
                    language: "pt-BR",
                    width: "100%",
                    theme: "bootstrap4",
                });
            }, 100);
            $("select[name='enableds[]']").prop('required',true);
        });
    </script>
@endpush
