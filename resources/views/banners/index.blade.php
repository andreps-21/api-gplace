@extends('layouts.app', ['page' => 'Banners', 'pageSlug' => 'banners'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-6">
                        <h4 class="card-title">Mídias/Anúncios</h4>
                    </div>
                    <div class="col-6 text-right">
                        <a href="{{ route('banners.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @include('alerts.success')
                @include('alerts.error')

                <div class="">
                    <table class="table tablesorter table-striped" id="">
                        <thead class=" text-primary">
                            <th scope="col">Nome</th>
                            <th scope="col">Tipo de Mídia</th>
                            <th scope="col">Posicão</th>
                            <th scope="col">Sequência</th>
                            <th scope="col">Ativo</th>
                            <th scope="col" class="text-right">Ação</th>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->types($item->type) }}</td>
                                <td>{{ $item->position_data }}</td>
                                <td>{{ $item->sequence }}</td>
                                <td>{{ $item->is_enabled ? "Sim" : "Não" }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('banners.destroy', $item->id) }}" method="post"
                                                id="form-{{$item->id}}">
                                                @csrf
                                                @method('delete')
                                                <a class="dropdown-item"
                                                    href="{{ route('banners.show', $item) }}">Visualizar</a>
                                                <a class="dropdown-item"
                                                    href="{{ route('banners.edit', $item) }}">Editar</a>
                                                <button type="button" class="dropdown-item btn-delete">
                                                    Excluir
                                                </button>
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
                    {{ $data->links() }}
                </nav>
            </div>
        </div>
    </div>
</div>
@include('banners._modal')
@endsection
