@extends('layouts.app', ['page' => 'Serviços x Fornecedores', 'pageSlug' => 'product-providers'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-8">
                        <h4 class="card-title">Serviços x Fornecedores</h4>
                    </div>
                    @can('product-providers_create')
                    <div class="col-4 text-right">
                        <a href="{{ route('product-providers.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                    </div>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @include('alerts.success')
                @include('alerts.error')

                <div class="">
                    <table class="table tablesorter table-striped" id="">
                        <thead class=" text-primary">
                            <th scope="col">Serviço</th>
                            <th scope="col">Fornecedor</th>
                            <th scope="col" class='text-right'>Vl. Serviço</th>
                            <th scope="col" class='text-right'>Repasse (%)</th>
                            <th scope="col">Ativo</th>
                            <th scope="col" class="text-right">Ação</th>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->product }}</td>
                                <td>{{ $item->provider }}</td>
                                <td class='text-right'>{{ $item->price }}</td>
                                <td class='text-right'>{{ floatToMoney($item->vl_transfer) }}</td>
                                <td >{{ floatToMoney($item->is_enabled) ? 'Sim' : 'Não' }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('product-providers.destroy', $item->id) }}" method="post"
                                                id="form-{{$item->id}}">
                                                @csrf
                                                @method('delete')
                                                @can('product-providers_view')
                                                <a class="dropdown-item"
                                                    href="{{ route('product-providers.show', $item) }}">Visualizar</a>
                                                @endcan
                                                @can('product-providers_edit')
                                                <a class="dropdown-item"
                                                    href="{{ route('product-providers.edit', $item) }}">Editar</a>
                                                @endcan
                                                @can('product-providers_delete')
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
                    {{ $data->links() }}
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection
