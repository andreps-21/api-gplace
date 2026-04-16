@extends('layouts.app', ['page' => 'Produtos/Serviços', 'pageSlug' => 'products'])
@push('css')
    <style>
        .modal-content {
            position: absolute;
        }

    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-header">
                    <div class="row">
                        <div class="col-8">
                            <h4 class="card-title">Produtos</h4>
                        </div>
                        @can('products_create')
                            <div class="col-4 text-right">
                                <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @include('alerts.success')
                    @include('alerts.error')
                    {!! Form::open()->fill(request()->all())->get() !!}
                    <div class="row">
                        <div class="col-md-6">
                            {!! Form::text('search', 'Pesquisar por nome') !!}
                        </div>
                        <div class="col-md-6">
                            {!! Form::select('section_id', 'Seção')->options($sections->prepend('Selecione', ''), 'name')->attrs(['class' => 'select2']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::select('type', 'Tipo', ['' => 'Selecione'] + \App\Models\Product::types()) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::select('brand_id', 'Marca')->options($brands->prepend('Selecione', ''), 'name')->attrs(['class' => 'select2']) !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::select('is_enabled', 'Ativo', ['' => 'Selecione', 1 => 'Sim', 0 => 'Não']) !!}
                        </div>
                        <div class="col-md-3 text-right">
                            <br>
                            <button class="btn btn-sm  btn-primary" style="font-size: 9px;" type="submit"><svg
                                    xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="currentColor"
                                    class="bi bi-funnel-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z" />
                                </svg> Filtrar</button>
                            <a id="clear-filter" style="font-size: 9px;" class="btn btn-sm btn-danger"
                                href="{{ route('products.index') }}"><svg xmlns="http://www.w3.org/2000/svg" width="9"
                                    height="9" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z" />
                                </svg> Limpar</a>
                        </div>

                    </div>
                    {!! Form::close() !!}
                    <div class="">
                        <p><b>Legenda:</b>
                            <span style="margin-right: 10px;">
                                <span class="dot" style="background-color:#1cc88a;"></span>
                                Ativo
                            </span>
                            <span style="margin-right: 10px; margin-left: 5px;">
                                <span class="dot" style="background-color:#e77a3b;"></span>
                                Inativo
                            </span>
                        </p>
                        <table class="table tablesorter table-striped" id="">
                            <thead class=" text-primary">
                                <th>#</th>
                                <th scope="col">Referência</th>
                                <th scope="col">Nome Comercial</th>
                                <th scope="col">Seção</th>
                                <th scope="col">Marca</th>
                                <th scope="col" class="text-right">Preço</th>
                                <th scope="col" class="text-right">Preço promocional</th>
                                <th scope="col" class="text-right">Ação</th>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>
                                            @if ($item->is_enabled)
                                                <span class="dot" style="background-color:#1cc88a;"></span>
                                            @else
                                                <span class="dot" style="background-color:#e77a3b;"></span>
                                            @endif
                                        </td>
                                        <td>{{ $item->reference }}</td>
                                        <td>{{ $item->commercial_name }}</td>
                                        <td>{{ $item->section }}</td>
                                        <td>{{ $item->brand }}</td>
                                        <td class="text-right">{{ floatToMoney($item->price) }}</td>
                                        <td class="text-right">{{ floatToMoney($item->promotion_price) }}</td>
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    <form action="{{ route('products.destroy', $item->id) }}"
                                                        method="post" id="form-{{ $item->id }}">
                                                        @csrf
                                                        @method('delete')
                                                        @can('products_view')
                                                            <a class="dropdown-item"
                                                                @if ($item->is_grid) onclick="verGrade('{{ $item->id }}','{{ $item->reference }}')" href="#" @else
                                                    href="{{ route('products.show', $item) }}" @endif>Visualizar</a>
                                                        @endcan
                                                        @can('products_edit')
                                                            <a class="dropdown-item"
                                                                href="{{ route('products.edit', $item) }}">Editar</a>
                                                        @endcan
                                                        @can('products_delete')
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
                    <nav class="d-flex justify-content-between" aria-label="...">
                        <label>N. Registros: {{ $data->total() }}</label>
                        {{ $data->appends(request()->all())->links() }}
                    </nav>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal -->
    <div class="modal fade" id="grade" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Grades</h4>
                    <button type="button" class="close" id="closeModal" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <small>Clique em um item para abrir as ações</small>
                    <div class="table-responsive">
                        <table class="table table-striped border" id="tablegrade">
                            <thead>
                                <tr>
                                    <td>Código</td>
                                    <th width="40%">Nome Comercial</th>
                                    <th width="20%">Preço</th>
                                    <th width="20%">Preço Promocional</th>
                                    <th width="10%">Estoque</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right"></td>
                                    <td class="text-right"></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="modalOpcoes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="exampleModalLabel">Ações</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        O que deseja fazer?
                    </div>
                    <div class="modal-footer">
                        <a href="#" id="productView" class="btn btn-secondary">Visualizar Produto</a>
                        <a href="#" id="productEdit" class="btn btn-primary">Editar Produto</a>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('js')
        <script>
            $('#tablegrade tbody').on('click', 'tr', function() {
                $('tr').each(function() {
                    $(this).css("background-color", "#fff")
                    $(this).find('td').each(function() {
                        $(this).css("color", "rgba(34, 42, 66, 0.7)")

                    })
                    trSelected = null
                });

                $(this).css("background-color", "#858796")
                $(this).find('td').each(function() {
                    $(this).css("color", "#fff")
                })

                trSelected = $(this);
                id = trSelected[0].children[0].innerText;
                if (id != '') {
                    $('#productEdit').attr('href', getUrl() + "/products/" + id + "/edit");
                    $('#productView').attr('href', getUrl() + "/products/" + id);
                    $('#modalOpcoes').modal('show');
                }
            });

            function verGrade(id, reference) {
                swal({
                        title: "Ver a Grade?",
                        text: "Clique em sim para visualizar a grade do produto!",
                        icon: "info",
                        buttons: ["Não", "Sim"],
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            $('#grade').modal('show');
                            $.ajax({
                                url: getUrl() + '/api/v1/variation',
                                method: 'post',
                                dataType: 'json',
                                data: {
                                    data: reference
                                },
                                success: function(res) {
                                    lista(res);
                                }
                            });
                        } else {
                            window.location.href = getUrl() + "/products/" + id;
                        }
                    });
            }

            function lista(res = []) {
                $("#tablegrade").dataTable().fnDestroy();
                $('#tablegrade').DataTable({
                    language: {
                        "sEmptyTable": "Nenhum registro encontrado",
                        "sInfo": "_START_ até _END_ de _TOTAL_ registros",
                        "sInfoEmpty": "0 - 0 de 0 registros",
                        "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                        "sInfoPostFix": "",
                        "sInfoThousands": ".",
                        "sLengthMenu": "_MENU_ resultados por página",
                        "sLoadingRecords": "Carregando...",
                        "sProcessing": "Processando...",
                        "sZeroRecords": "Nenhum registro encontrado",
                        "sSearch": "Pesquisar",
                        "oPaginate": {
                            "sNext": "Próximo",
                            "sPrevious": "Anterior",
                            "sFirst": "Primeiro",
                            "sLast": "Último"
                        },
                        "oAria": {
                            "sSortAscending": ": Ordenar colunas de forma ascendente",
                            "sSortDescending": ": Ordenar colunas de forma descendente"
                        }
                    },
                    "lengthMenu": [
                        [5, 25, 50, -1],
                        [5, 25, 50, "Todos"]
                    ],
                    "aaData": res,
                    "aoColumns": [{
                            'mDataProp': 'id'
                        },
                        {
                            "mDataProp": "commercial_name"
                        },
                        {
                            "mDataProp": "price"
                        },
                        {
                            "mDataProp": "promotion_price"
                        },
                        {
                            "mDataProp": "quantity"
                        },

                    ]
                });
            }
        </script>
    @endpush
