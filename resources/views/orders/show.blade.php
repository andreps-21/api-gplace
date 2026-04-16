@extends('layouts.app', ['page' => 'Pedido', 'pageSlug' => 'orders'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                    </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('orders.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="painel-header">
                                                <div class="row">
                                                    <div class="col-md-2 mb-4">
                                                        <p><strong>Numero do Pedido: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->code }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p><strong>Dt. Compra: </strong></p>
                                                        <p class="card-text">
                                                            {{ carbon($item->purchase_date)->format('d/m/Y H:i') }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p><strong>Status: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->status($item->status) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p><strong>Cod. Rastreio: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->tracking_code }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p><strong>Cliente: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->customer->people->name }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <p><strong>Vendedor: </strong></p>
                                                        <p class="card-text">
                                                            {{ isset($item->salesman) ? $item->salesman->people->name : null}}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <ul class="nav nav-tabs" style="margin-top:30px">
                                                <li class="nav-item">
                                                    <a style="background-color: white" data-toggle="tab"
                                                        class="nav-link active" href="#home"><span>Itens</span></a>
                                                </li>
                                            </ul>

                                            <div class="tab-content">
                                                <div id="home" class="tab-pane fade show active">
                                                    <div class="painel-body">
                                                        <div class="row">
                                                            <div class="table-responsive">
                                                                <table id="tabela" class="table table-dynamic">
                                                                    <thead>
                                                                        <tr class="conteudo-th">
                                                                            <th class="data">Produto/Serviço</th>
                                                                            <th class="values">Un. Medida</th>
                                                                            <th class="values">Qtde</th>
                                                                            <th class="values">Vl.Unit</th>
                                                                            <th class="values">Descontos</th>
                                                                            <th class="values">Total do Item(R$)</th>
                                                                            <th class="values">IPI ($)</th>
                                                                            <th class="values">ICMS ($)</th>
                                                                            <th class="values">Pontos</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($item->items as $itemOrder)
                                                                        <tr class="dynamic-form">
                                                                            <td>
                                                                                <div class="form-group" style="width:250px">
                                                                                    <input type="text"
                                                                                        value="{{ $itemOrder->product->commercial_name . ' - '. $itemOrder->product->sku }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ $itemOrder->um }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ floatToMoney($itemOrder->quantity) }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ floatToMoney($itemOrder->value_unit) }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ floatToMoney($itemOrder->discount) }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                             <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ floatToMoney($itemOrder->total) }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ floatToMoney($itemOrder->ipi) }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ floatToMoney($itemOrder->icms) }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="form-group">
                                                                                    <input type="text"
                                                                                        value="{{ floatToMoney($itemOrder->spots) }}"
                                                                                        class="form-control" readonly>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="painel-footer">
                                                <div class="row">
                                                    <div class="col-md-2 mb-4">
                                                        <p><strong>Total de produtos: </strong></p>
                                                        <p class="card-text">
                                                            {{ floatToMoney($item->vl_amount) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <p><strong>ICMS: </strong></p>
                                                        <p class="card-text">
                                                            {{ floatToMoney($item->vl_icms) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <p><strong>IPI: </strong></p>
                                                        <p class="card-text">
                                                            {{ floatToMoney($item->vl_ipi) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <p><strong>Frete: </strong></p>
                                                        <p class="card-text">
                                                            {{ floatToMoney($item->vl_freight) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <p><strong>Desconto: </strong></p>
                                                        <p class="card-text">
                                                            {{ floatToMoney($item->vl_discount) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <p><strong>Vl.Total do Pedido: </strong></p>
                                                        <p class="card-text">
                                                            {{ floatToMoney($item->total) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <p><strong>Vl.Total do Pontos: </strong></p>
                                                        <p class="card-text">
                                                            {{ floatToMoney($item->vl_spots) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-4 mb-4">
                                                        <p><strong>Formas de pagto: </strong></p>
                                                        <p class="card-text">
                                                            {{$item->payment->info}}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p><strong>Cond. de Pagamento: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->payment_condition }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <p><strong>Cupom: </strong></p>
                                                        <p class="card-text">
                                                            {{ optional($item->coupon)->name }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-3 mb-4">
                                                        <p><strong>Tipo de entrega: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->types($item->type) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <p><strong>Local de entrega: </strong></p>
                                                        <p class="card-text">
                                                            @if($item->address)
                                                            {{ $item->address->street }}, {{ $item->address->number }},
                                                            {{ $item->address->complement }}. {{ $item->address->district }}.
                                                            {{ $item->address->city->title }} - {{ $item->address->city->letter }}.
                                                            {{ $item->address->reference }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <p><strong>Observação: </strong></p>
                                                        <p class="card-text">
                                                            {{ $item->description }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
