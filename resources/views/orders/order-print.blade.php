@extends('layouts.app', ['page' => 'Imprimir pedido', 'pageSlug' => 'print'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="content section-top print">
                <style>
                    .table-header
                    {
                        display: flex;
                        justify-content: center;
                        border-bottom: 1px solid #E3E1E1;
                        height: 130px;
                        padding-left: 20px;
                        padding-right: 20px;
                    }
                    .card-body
                    {
                        padding-left: 20px;
                        padding-right: 20px;
                    }

                    .card-user, .order, .obs
                    {
                        border-bottom: 0.5px solid #E3E1E1;
                        padding: 20px;
                    }

                    .card-order
                    {
                        padding: 20px;
                    }

                    .kcc-label, .card-order-nav
                    {
                        width: 65%;
                    }

                    .table-order
                    {
                        width: 100%;
                    }

                    .table-item
                    {
                        width: 100%;
                    }

                    .th-text
                    {
                        font-weight: normal;

                    }

                    #text
                    {
                        background-color: #E3E1E1;
                    }

                    .card-order-nav
                    {
                        /* margin-left: 81.5%; */
                        width: 18%;
                        /* margin-top: -9%; */
                    }

                    /* .kcc-label
                    {
                        margin-left: 15%;
                        margin-top: -7.5%;
                    } */

                    .img-logo
                    {
                        max-height: 130px;
                        max-width: 60%;
                        min-width: 60%;
                    }

                    .img-nav
                    {
                        margin-left: 2%;
                    }

                    .img-header
                    {
                        max-width: 100%;
                        min-width: 12%;
                        padding-right: 20px;
                        max-height: 95%;
                        min-height: auto;
                    }

                    .img-footer
                    {
                        margin-left: 85%;
                        margin-top: -30px;
                    }

                    .text-footer
                    {
                        margin-left: 71%;
                        margin-top: -25px;
                    }

                    .card-footer
                    {
                        padding-left: 20px;
                        padding-right: 20px;
                    }


                </style>
                <div class="card-header no-print" style="padding: 15px 35px 0;">
                    <div class="row">
                        <div class="ml-auto mr3 text-right">

                            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-primary" style="padding: 7px 45px; box-shadow: 1px 3px 10px rgb(0 0 0 / 30%);">Voltar</a>

                            <button class="btn btn-info btn-sm btn-print" style="padding: 7px 55px; box-shadow: 1px 3px 10px rgb(0 0 0 / 30%);">
                                <i class="fas fa-print fa-lg"></i>
                            </button>

                        </div>
                    </div>
                </div>
                <div id="wrapper">
                    <div class="headReport" style="padding-top:1rem">
                        <div class="table-header">

                            <div class="img-nav">
                                @if ($item->store->setting->logo)
                                    <img class="img-header" src="{{ asset('storage/' . $item->store->setting->logo) }}"/>
                                @else
                                    <img class="img-header" src="{{ asset('images/noimage.png')}}"/>
                                @endif
                            </div>

                            <table class="kcc-label">
                                <thead>
                                    <tr>
                                        <th>{{ $item->store->setting->name }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td class="cpf_cnpj">{{ $item->store->setting->nif }}</td></tr>
                                    <tr><td>{{ $item->store->setting->email }}/ <span class="phone">{{ $item->store->setting->phone }}</span></td></tr>
                                    <tr>
                                        <td>
                                            @if($item->store->setting->address)
                                            {{ $item->store->setting->address }}, {{ $item->store->setting->number }},
                                            {{ $item->store->setting->complement }}. {{ $item->store->setting->district }}.
                                            {{ $item->store->setting->city->title }} - {{ $item->store->setting->city->state->letter }}.
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="card-order-nav">
                                <thead>
                                    <tr>
                                        <th>Pedido:  {{ $item->code }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Dt. Pedido: {{  carbon($item->purchase_date)->format('d/m/Y') }}</td></tr>
                                    <tr><td>Emissão: {{ carbon($item->purchase_date)->format('d/m/Y H:i') }}</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-body" >
                            <div class="card-user">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Dados do Cliente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>{{ $item->customer->people->formal_name }}</td><td>  CNPJ/CPF: <span class="cpf_cnpj"> {{ $item->customer->people->nif }} </span></td></tr>
                                        <tr><td> {{ $item->customer->people->email }}/ <span class="phone" >{{ $item->customer->people->phone }} </span></td></tr>
                                        <tr>
                                            <td>
                                                @if($item->customer->people->street)
                                                    {{ $item->customer->people->street }}, {{ $item->customer->people->number }},
                                                    {{ $item->customer->people->district }}. {{ $item->customer->people->city->title }}
                                                    - {{ $item->customer->people->city->state->letter }}.
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="card-order">
                                <strong>Itens do Pedido</strong>
                                <table class="table-item">
                                    <thead>
                                        <tr id="text">
                                            <th>Item</th>
                                            <th>Produto</th>
                                            <th>Cod ERP</th>
                                            <th>Qtde</th>
                                            <th>Preço un (R$)</th>
                                            <th>Total (R$)</th>
                                        </tr>
                                    </thead>
                                    @foreach ($item->items as $itemOrder)
                                    <tbody>
                                        <td> {{ isset($i) ? ++$i : $i = 1}}</td>
                                        <td>{{ $itemOrder->product->commercial_name}}</td>
                                        <td>{{ $itemOrder->erp_code }}</td>
                                        <td>{{ floatToMoney($itemOrder->quantity) }}</td>
                                        <td>{{ floatToMoney($itemOrder->value_unit) }}</td>
                                        <td>{{ floatToMoney($itemOrder->total) }}</td>
                                    </tbody>
                                    @endforeach
                                    <tbody>
                                        <td id="text">Totais</td>
                                        <td id="text"></td>
                                        <td id="text"></td>
                                        <td id="text"> {{ $quant }} </td>
                                        <td id="text"> {{ floatToMoney($item->vl_amount) }}</td>
                                        <td id="text"> {{ floatToMoney($item->total) }}</td>
                                    </tbody>
                                </table>
                            </div>

                            <div class="order">
                                <strong>Resumo do pedido</strong>
                                <table class="table-order">
                                    <thead>
                                        <tr>
                                            <th class="th-text">Total de produtos:  {{ floatToMoney($item->vl_amount) }} </th>
                                            <th class="th-text">Desconto: {{ floatToMoney($item->vl_discount) }} </th>
                                            <th class="th-text">Frete: {{ floatToMoney($item->vl_freight) }} </th>
                                            <th class="th-text">Total do Pedido: {{ floatToMoney($item->total) }}</th>
                                        </tr>
                                        <tr>
                                            <th class="th-text">Pagamento: {{$item->payment->info}}</th>
                                            <th class="th-text">Condição de Pagamento: {{ $item->payment_condition }}</th>
                                        </tr>
                                        <tr>
                                            <th class="th-text">
                                                Endereço:
                                                @if($item->address)
                                                {{ $item->address->street }}, {{ $item->address->number }},
                                                {{ $item->address->complement }}. {{ $item->address->district }}.
                                                {{ $item->address->city->title }} - {{ $item->address->city->letter }}.
                                                {{ $item->address->reference }}
                                                @endif
                                            </th>
                                        </tr>
                                    </thead>

                                </table>
                            </div>

                            <div class="obs">
                                <strong>Observação:  {{ $item->description }}</strong>
                            </div>
                            <div class="card-footer ">
                                <p>www.dix.digital</p>
                                <p class="text-footer"> Desenvolvido por:</p>
                                <div class="img-footer">
                                    <img class ="img-logo" src="{{  asset('images/logoDix.png') }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

