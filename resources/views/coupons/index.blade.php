@extends('layouts.app', ['page' => 'Cupons', 'pageSlug' => 'coupons'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="card-title">Cupons</h4>
                    </div>
                    @can('coupons_create')
                    <div class="col-md-4 text-right">
                        <a href="{{ route('coupons.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                    </div>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @include('alerts.success')
                @include('alerts.error')

                <form>
                    <div class="row">
                        <div class="col-md-3">
                            {!!Form::date('start_at', 'Dt. Venc. Início')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::date('end_at', 'Dt. Venc. Fim')
                            !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::select('is_enabled', 'Ativo', ['' => 'Selecione', 1 => 'Sim', 0 => 'Não'])
                            !!}
                        </div>
                        <div class="col-md-3">
                            <br>
                            <button class="btn btn-sm  btn-primary" style="font-size: 9px;" type="submit"><svg
                                    xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="currentColor"
                                    class="bi bi-funnel-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z" />
                                </svg> Filtrar</button>
                            <a id="clear-filter" style="font-size: 9px;" class="btn btn-sm btn-danger"
                                href="{{ route('coupons.index') }}"><svg xmlns="http://www.w3.org/2000/svg" width="9"
                                    height="9" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z" />
                                </svg> Limpar</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive-xl mt-3">
                    <table class="table table-striped">
                        <caption>N. Registros: {{ $data->total() }}</caption>
                        <thead class=" text-primary">
                            <th scope="col">Chave</th>
                            <th scope="col">Dt. Início</th>
                            <th scope="col">Dt. Fim</th>
                            <th scope="col" class="text-right">Valor</th>
                            <th scope="col" class="text-right">Qtd.</th>
                            <th scope="col" class="text-right">Qtd. Usado</th>
                            <th scope="col" class="text-right">Ação</th>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ carbon($item->start_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ carbon($item->end_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-right">{{ floatToMoney($item->discount) }}</td>
                                <td class="text-right">{{ $item->quantity }}</td>
                                <td class="text-right">{{ $item->quantity - $item->balance }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('coupons.destroy', $item->id) }}" method="post"
                                                id="form-{{$item->id}}">
                                                @csrf
                                                @method('delete')
                                                @can('coupons_view')
                                                <a class="dropdown-item"
                                                    href="{{ route('coupons.show', $item) }}">Visualizar</a>
                                                @endcan
                                                @can('coupons_edit')
                                                    <a id="{{ $item->id }}"
                                                    class="dropdown-item inactivate-coupon"
                                                    style="color: #9A9A9A; cursor: pointer;"
                                                    >Inativar</a>
                                                @endcan
                                                @if($item->balance == $item->quantity)
                                                @can('coupons_edit')
                                                    <a class="dropdown-item"
                                                    href="{{ route('coupons.edit', $item) }}">Editar</a>
                                                @endcan
                                                @can('coupons_delete')
                                                <button type="button" class="dropdown-item btn-delete">
                                                    Excluir
                                                </button>
                                                @endcan
                                                @endif
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
@endsection

@push('js')
    <script>
        $('.inactivate-coupon').on('click', function () {
            var id = $(this).attr('id');
            $.get(getUrl() + "/api/v1/inactivate-coupon/" + id, function(data){
                var msg = data.responseText ?? 'Cupom inativado com sucesso.';
                swal('Sucesso', msg, 'success')
                .then(function() {
                    window.location.reload();
                });
            })
            .fail(function (data) {
                var msg = data.responseText ?? 'Algo deu errado.';
                swal('Erro!', msg , 'error');
            })
        });
    </script>
@endpush
