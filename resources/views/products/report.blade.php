@extends('layouts.app', ['page' => 'Produtos/Serviços', 'pageSlug' => 'products'])


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-8">
                            <h4 class="card-title">Relatório de Produtos</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="product">Do Produto</label>
                                <select name="start_product" class="form-control product select2 " required>
                                    <option value="">Selecione</option>
                                    @foreach ($products as $key => $product)
                                        <option @if (request()->start_product == $key) {{ 'selected' }} @endif
                                            value="{{ $key }}">{{ $product->commercial_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="product">até Produto</label>
                                <select name="end_product" class="form-control product select2" required>
                                    <option value="">Selecione</option>
                                    @foreach ($products as $key => $product)
                                        <option @if (request()->end_product == $key) {{ 'selected' }} @endif
                                            value="{{ $key }}">{{ $product->commercial_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                {!! Form::date('start_date', 'Dt. Inicio')->required() !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::date('end_date', 'Dt. Fim')->required() !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::select('brand', 'Marca')->options($brands->prepend('Selecione', ''))->attrs(['class' => 'select2']) !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::select('section_id', 'Seção')->options($sections->prepend('Selecione', ''), 'name')->attrs(['class' => 'select2']) !!}
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
                                                    <h2 style="margin-top: 0px;">Produtos Vendidos</h2>
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
                                                        <th>Produto</th>
                                                        <th class="text-left">Cliente</th>
                                                        <th class="text-right">Data</th>
                                                        <th class="text-right">Qtde</th>
                                                        <th class="text-right">Situação</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($data as $item)
                                                        <tr>
                                                            <td>{{ $item->commercial_name }}</td>
                                                            <td class="text-left">{{ $item->name }}</td>
                                                            <td class="text-right">
                                                                {{ carbon($item->created_at)->format('d/m/Y') }}</td>
                                                            <td class="text-right">
                                                                {{ number_format($item->quantity, '2', ',', '.') }}</td>
                                                            <td class="text-right">{{ 'Comprado' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5">Nenhum dado encontrado!</td>
                                                        </tr>
                                                    @endforelse
                                                    <tr class="border-top">
                                                        <th colspan="3" class="text-right"> Total:</th>
                                                        <th colspan="1" class="text-right">
                                                            {{ number_format($data->sum('quantity'), 2, ',', '.') }}</th>
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
