@extends('layouts.app', ['page' => 'Pedido de venda', 'pageSlug' => 'orders'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="card-title">Criar Pedido de Venda</h4>
                    </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {!!Form::open()
                    ->post()
                    ->route('orders.store')
                    ->multipart()!!}
                    <div class="pl-lg-4">
                        @include('orders._forms')
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {

        $('body').on('change', '.product', function(){
            var um = $(this).find(':selected').data('um')
            $(this).closest('td').next().find('input').val(um)
        })

        $('body').on('blur', '.ipi', function(){
            calculationIpi();
        });
        $('body').on('blur', '.spots', function(){
            calculationSpot();
        });
        $('body').on('blur', '.icms', function(){
            calculationIcms();
        });

        $('#inp-vl_freight').on('blur', function(){
            calculationTotal();
        });

        $('body').on('blur', '.value', function(){
            var $total_amount = $(this).closest('tr').find('.total');
            var qtd = convertMoedaToFloat($(this).closest('tr').find('.quantity').val());
            var discount = convertMoedaToFloat($(this).closest('tr').find('.discount').val());
            var value = convertMoedaToFloat($(this).val());
            var total_amount = convertFloatToMoeda(parseFloat((qtd*value) - discount));
            $total_amount.val(total_amount);
            calculationValue();
        });

        $('body').on('blur', '.quantity', function(){
            var $total_amount = $(this).closest('tr').find('.total');
            var value =  convertMoedaToFloat($(this).closest('tr').find('.value').val());
            var qtd =  convertMoedaToFloat($(this).val());
            var discount = convertMoedaToFloat($(this).closest('tr').find('.discount').val());
            var total_amount = convertFloatToMoeda(parseFloat((qtd*value) - discount));
            $total_amount.val(total_amount);
            calculationValue();
        });

        $('body').on('blur', '.discount', function(){
            var $total_amount = $(this).closest('tr').find('.total');
            var value =  convertMoedaToFloat($(this).closest('tr').find('.value').val());
            var discount =  convertMoedaToFloat($(this).val());
            var qtd = convertMoedaToFloat($(this).closest('tr').find('.quantity').val());
            var total_amount = convertFloatToMoeda(parseFloat((qtd*value) - discount));
            $total_amount.val(total_amount);
            calculationValue();
            calculationDiscount();
        });

    });
    function calculationValue(){
        var total_amount = 0;
        $('.total').each(function( index ) {
            total_amount +=  convertMoedaToFloat($(this).val());
        });
        $('#inp-vl_amount').val(convertFloatToMoeda(parseFloat(total_amount)));
        calculationTotal();
    }

    function calculationTotal(){
        var freight = convertMoedaToFloat($('#inp-vl_freight').val());
        var ipi = convertMoedaToFloat($('#inp-vl_ipi').val());
        var icms = convertMoedaToFloat($('#inp-vl_icms').val());
        var amount = convertMoedaToFloat($('#inp-vl_amount').val());
        var total_amount =  amount + ipi + icms + freight;
        $('#inp-vl_total').val(convertFloatToMoeda(parseFloat(total_amount)));
    }

    function calculationIpi(){
        var total_ipi = 0;
        $('.ipi').each(function( index ) {
            total_ipi +=  convertMoedaToFloat($(this).val());
        });
        $('#inp-vl_ipi').val(convertFloatToMoeda(parseFloat(total_ipi)));
        calculationTotal();
    }

    function calculationDiscount(){
        var total_discount = 0;
        $('.discount').each(function( index ) {
            total_discount +=  convertMoedaToFloat($(this).val());
        });
        $('#inp-vl_discount').val(convertFloatToMoeda(parseFloat(total_discount)));
        calculationTotal();
    }

    function calculationIcms(){
        var total_icms = 0;
        $('.icms').each(function( index ) {
            total_icms +=  convertMoedaToFloat($(this).val());
        });
        $('#inp-vl_icms').val(convertFloatToMoeda(parseFloat(total_icms)));
        calculationTotal();
    }

    function calculationSpot(){
        var total_spots = 0;
        $('.spots').each(function( index ) {
            total_spots +=  convertMoedaToFloat($(this).val());
        });
        $('#inp-vl_spots').val(convertFloatToMoeda(parseFloat(total_spots)));
        calculationTotal();
    }
</script>
@endpush
