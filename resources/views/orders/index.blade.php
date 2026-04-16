@extends('layouts.app', ['page' => 'Pedidos', 'pageSlug' => 'orders'])

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">Pedido de Venda</h4>
                        </div>
                        {{-- @can('orders_create')
                            <div class="col-4 text-right">
                                <a href="{{ route('orders.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                            </div>
                        @endcan --}}
                    </div>
                </div>
                <div class="card-body">
                    @include('alerts.success')
                    @include('alerts.error')
                    {!! Form::open()->get()->fill(request()->all()) !!}
                    <div class="row">
                        <div class="col-md-6">
                            {!! Form::select('customer', 'Cliente', [null => 'Selecione...'] + $customers->pluck('info', 'id')->all())->attrs(['class' => 'select2']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::select('sync', 'Sincronizado', ['' => 'Selecione', 'true' => 'Sim', 'false' => 'Não']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::select('status', 'Status', [null => 'Selecione...'] + \App\Models\Order::status())
                            ->id('status') !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::date('start_date', 'Dt. Inicio')->attrs(['class' => 'dat start_date']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::date('end_date', 'Dt. Fim')->attrs(['class' => 'dat end_date']) !!}
                        </div>


                        <div class="col-md-6 text-right">
                            <br>
                            <br>
                            <button class="btn btn-sm  btn-primary" style="font-size: 9px;" type="submit"><svg
                                    xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="currentColor"
                                    class="bi bi-funnel-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z" />
                                </svg> Filtrar</button>
                            <a id="clear-filter" style="font-size: 9px;" class="btn btn-sm btn-danger"
                                href="{{ route('orders.index') }}"><svg xmlns="http://www.w3.org/2000/svg" width="9"
                                    height="9" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z" />
                                </svg> Limpar</a>
                        </div>
                    </div><br>
                    <div class="row">
                        <div class="col-12">
                            <p style="font-size: 10px;"><b>Legenda:</b>
                                <span style="margin-right: 5px; margin-left: 5px;">
                                    <span class="dot" style="background-color:#e77a3b;"></span>
                                    Em aprovação
                                </span>
                                <span style="margin-right: 5px;">
                                    <span class="dot" style="background-color:#1cc88a;"></span>
                                    Aprovado
                                </span>
                                <span style="margin-right: 5px;">
                                    <span class="dot" style="background-color:#ff0000;"></span>
                                    Pendente
                                </span>
                                <span style="margin-right: 5px; margin-left: 5px;">
                                    <span class="dot" style="background-color:#f6c23e;"></span>
                                    Em faturamento
                                </span>
                                <span style="margin-right: 5px; margin-left: 5px;">
                                    <span class="dot" style="background-color:#36b9cc;"></span>
                                    Em expedição
                                </span>
                                <span style="margin-right: 5px;">
                                    <span class="dot" style="background-color: #9e4edf;"></span>
                                    Despachado
                                </span>
                                <span style="margin-right: 5px;">
                                    <span class="dot" style="background-color: #4e73df;"></span>
                                    Entregue
                                </span>
                                <span style="margin-right: 5px;">
                                    <span class="dot" style="background-color: #000000;"></span>
                                    Cancelado
                                </span>
                            </p>
                        </div>
                    </div>
                    {!! Form::close() !!}
                    <div>
                        <table class="table tablesorter table-striped" id="">
                            <caption>N. Pedidos: {{ $data->total() }}</caption>
                            <thead class=" text-primary">
                                <th scope="col">#</th>
                                <th scope="col">Código</th>
                                <th scope="col">Cliente</th>
                                <th scope="col" class="text-center">Dt. Compra</th>
                                <th scope="col" class="text-center">Horario Do Pedido</th>
                                <th scope="col" class="text-right">Valor Total</th>
                                <th scope="col" class="text-right">Ação</th>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr style="font-size: 11px;">
                                        <td class="text-center">
                                            @if ($item->status == 1)
                                                <span class="dot" style="background-color: #e77a3b;"></span>
                                            @elseif ($item->status == 2)
                                                <span class="dot" style="background-color: #1cc88a;"></span>
                                            @elseif ($item->status == 3)
                                                <span class="dot" style="background-color: #ff0000;"></span>
                                            @elseif ($item->status == 4)
                                                <span class="dot" style="background-color: #fffc61;"></span>
                                            @elseif ($item->status == 5)
                                                <span class="dot" style="background-color: #36b9cc;"></span>
                                            @elseif ($item->status == 6)
                                                <span class="dot" style="background-color: #9e4edf;"></span>
                                            @elseif ($item->status == 7)
                                                <span class="dot" style="background-color: #4e73df;"></span>
                                            @elseif ($item->status == 8)
                                                <span class="dot" style="background-color: #000000;"></span>
                                            @endif
                                        </td>
                                        <td>{{ $item->code }}</td>
                                        <td>{{ $item->customer->people->name }}</td>
                                        <td class="text-center">{{ carbon($item->purchase_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="text-center">{{ carbon($item->purchase_date)->format('H:i') }}</td>
                                        <td class="text-right">{{ floatToMoney($item->total) }}</td>
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    <form action="{{ route('orders.destroy', $item->id) }}" method="post"
                                                        id="form-{{ $item->id }}">
                                                        @csrf
                                                        @method('delete')
                                                        @can('orders_view')
                                                            <a class="dropdown-item"
                                                                href="{{ route('orders.show', $item) }}">Visualizar</a>
                                                        @endcan
                                                        @can('orders_view')
                                                            <a class="dropdown-item"
                                                                href="{{ route('orders.print', $item) }}">Imprimir Pedido</a>
                                                        @endcan
                                                        @can('orders_edit')
                                                            <a class="dropdown-item" href="#" data-id="{{ $item->id }}"
                                                                data-status="{{ $item->status }}" data-toggle="modal"
                                                                data-target="#statusModal">
                                                                Mudar Status</a>
                                                        @endcan
                                                        @can('orders_delete')
                                                            <button type="button" class="dropdown-item btn-delete">
                                                                Excluir
                                                            </button>
                                                        @endcan
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="20" style="text-align: center; font-size: 1.1em;">
                                            Nenhuma informação cadastrada.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-4">
                    <nav class="d-flex justify-content-end" aria-label="...">
                        {{ $data->appends(request()->all())->links() }}
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="statusModalLabel">Alterar Status do Pedido</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-status">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="status-id">
                        <div class="row">
                            <div class="col-12">
                                {!! Form::select('status', 'Status', [null => 'Selecione...'] + \App\Models\Order::status())->required() !!}
                            </div>
                            <div class="col-12 d-none" id="div-tracking-code">
                                {!! Form::text('tracking_code', 'Cod. Rastreio')->attrs(['maxlength' => 20]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $('#statusModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget)
            var id = button.data('id')
            var status = button.data('status')
            var modal = $(this)
            modal.find('.modal-body #status-id').val(id)
            $('#inp-status').children().each(function() {
                $(this).prop("disabled", $(this).attr('value') <= status);
            })
        })

        $('#inp-status').on('change', function() {
            $('#div-tracking-code').addClass('d-none')
            if ($(this).val() == 6) {
                $('#div-tracking-code').removeClass('d-none')
                $('#inp-tracking_code').val("")
            }
            $('#inp-tracking_code').prop('required', $(this).val() == 6)
        })

        $('#form-status').on('submit', function(event) {
            event.preventDefault();

            $.post(getUrl() + '/api/v1/public/change-status-orders', $(this).serialize())
                .done(function() {
                    swal('Sucesso', "Mudança de status realizada com sucesso.", 'success')
                        .then(function() {
                            location.reload()
                        })
                })
                .fail(function() {
                    console.log(this)
                    swal('Error', "Houve um erro na mudança do status.", 'error')
                        .then(function() {
                            location.reload()
                        })
                })
        })
    </script>
@endpush
