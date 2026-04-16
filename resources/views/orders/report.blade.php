@extends('layouts.app', ['page' => 'Pedidos', 'pageSlug' => 'orders'])


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-8">
                            <h4 class="card-title">Relatório de Pedidos</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="">Do Cliente</label>
                                <select name="start_customer_id" id="start_customer_id" class="form-control select2">
                                    @forelse ($customers as $key => $item)
                                        <option @if (request()->start_customer_id == $key) {{ 'selected' }} @endif
                                            value="{{ $key }}">{{ $item->name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="">Até Cliente</label>
                                <select name="end_customer_id" id="end_customer_id" class="form-control select2">
                                    @forelse ($customers as $key => $item)
                                        <option @if (request()->end_customer_id == $key) {{ 'selected' }} @endif
                                            value="{{ $key }}">{{ $item->name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-3">
                                {!! Form::date('start_date', 'Dt. Inicio Pedido')->required() !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::date('end_date', 'Dt. Fim Pedido')->required() !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::select('status', 'Status', [null => 'Selecione...'] + \App\Models\Order::status())->id('status') !!}
                            </div>
                            <div class="col-md-3 text-right">
                                <br>
                                <button class="btn btn-primary" type="submit">Gerar Relatorio</button>
                            </div>
                        </div>
                    </form>
                    @if (isset($data) && !empty($data))
                        <div class="row mt-4" style="margin: 0 1.5rem">
                            <div class="col-12">
                                <div class="content section-top print">
                                    <div class="text-right no-print">
                                        <button class="btn btn-info btn-sm float-right btn-print">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                    <div id="wrapper">
                                        <div class="headReport" style="padding-top:1rem">
                                            <!-- <div style="width: 33%">logo

                                        </div> -->
                                            <div style="text-align: center;">
                                                <div class="col-md-12">
                                                    <h2 style="margin-top: 0px;">Pedidos Por Cliente</h2>
                                                </div>

                                                <div class="col-md-12 text-right">
                                                    <span> De {{ carbon(request()->start_date)->format('d/m/Y') }}</span>
                                                    <span> &nbsp; até
                                                        {{ carbon(request()->end_date)->format('d/m/Y') }}</span>
                                                </div>
                                            </div>
                                            <br>
                                            <table class="table table-sm table-borderless accounts">
                                                <thead class="border-bottom border-top">
                                                    <tr>
                                                        <th class="text-left">Pedido</th>
                                                        <th class="text-left" width="20%">Cliente</th>
                                                        <th class="text-right">Dt Compra</th>
                                                        <th class="text-right" width="10%">Vl Pedido</th>
                                                        <th class="text-right" width="10%">Vl Frete</th>
                                                        <th width="10%">F. Pagto</th>
                                                        <th>Cupons</th>
                                                        <th>Rastreio</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($data as $item)
                                                        <tr>
                                                            <td class="text-left">{{ $item->code }}</td>
                                                            <td>{{ $item->name }}</td>
                                                            <td class="text-right">
                                                                {{ carbon($item->created_at)->format('d/m/Y') }}</td>
                                                            <td class="text-right">
                                                                {{ number_format($item->vl_amount, 2, ',', '.') }}</td>
                                                            <td class="text-right">
                                                                {{ number_format($item->vl_freight, 2, ',', '.') }}</td>
                                                            <td>{{ $item->payment }}</td>
                                                            <td>{{ $item->coupom }}</td>
                                                            <td>{{ $item->tracking_code }}</td>
                                                            <td class="text-left">
                                                                {{ App\Models\Order::status($item->status) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5">Nenhum dado encontrado!</td>
                                                        </tr>
                                                    @endforelse
                                                    <tr class="border-top">
                                                        <th colspan="2" class="text-right"> Total:</th>
                                                        <th colspan="2" class="text-right">
                                                            {{ number_format($data->sum('vl_amount'), 2, ',', '.') }} </th>
                                                        <th colspan="1" class="text-right">
                                                            {{ number_format($data->sum('vl_freight'), 2, ',', '.') }}</th>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
