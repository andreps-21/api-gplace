@extends('layouts.app', ['page' => 'Redes Sociais', 'pageSlug' => 'social-medias'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-8">
                        <h4 class="card-title">Redes Sociais</h4>
                    </div>
                    @can('social-medias_create')
                    <div class="col-4 text-right">
                        <a href="{{ route('social-medias.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
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
                            <th scope="col">Código</th>
                            <th scope="col">Descrição</th>
                            <th scope="col">Ativo</th>
                            <th scope="col" class="text-right">Ação</th>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->is_enabled ? 'Sim' : 'Não' }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('social-medias.destroy', $item->id) }}" method="post"
                                                id="form-{{$item->id}}">
                                                @csrf
                                                @method('delete')
                                                @can('social-medias_view')
                                                <a class="dropdown-item"
                                                    href="{{ route('social-medias.show', $item) }}">Visualizar</a>
                                                @endcan
                                                @can('social-medias_edit')
                                                <a class="dropdown-item"
                                                    href="{{ route('social-medias.edit', $item) }}">Editar</a>
                                                @endcan
                                                @can('social-medias_delete')
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
