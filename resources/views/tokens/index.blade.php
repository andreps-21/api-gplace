@extends('layouts.app', ['page' => 'Familias', 'pageSlug' => 'tokens'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-8">
                        <h4 class="card-title">Tokens de Integração</h4>
                    </div>
                    @can('tokens_create')
                    <div class="col-4 text-right">
                        <a href="{{ route('tokens.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
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
                            <th scope="col">Loja</th>
                            <th scope="col">Descrição</th>
                            <th scope="col">Expiração</th>
                            <th scope="col" class="text-right">Ação</th>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->store->people->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->expires_at->format('d/m/Y H:i') }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('tokens.destroy', $item->id) }}" method="post"
                                                id="form-{{$item->id}}">
                                                @csrf
                                                @method('delete')
                                                @can('tokens_view')
                                                <a class="dropdown-item"
                                                    href="{{ route('tokens.show', $item) }}">Visualizar</a>
                                                @endcan
                                                @can('tokens_edit')
                                                <a class="dropdown-item"
                                                    href="{{ route('tokens.edit', $item) }}">Editar</a>
                                                @endcan
                                                @can('tokens_delete')
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
