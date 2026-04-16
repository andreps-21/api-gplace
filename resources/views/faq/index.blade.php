@extends('layouts.app', ['page' => 'FAQ', 'pageSlug' => 'faq'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="card-title">FAQ</h4>
                    </div>
                    @can('sections_create')
                    <div class="col-md-4 text-right">
                        <a href="{{ route('faq.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                    </div>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @include('alerts.success')
                @include('alerts.error')

                <div class="table-responsive-xl">
                    <table id="tree-table" class="table table-striped">
                        <thead class=" text-primary">
                            <th scope="col">Pergunta</th>
                            <th scope="col">Ativo</th>
                            <th scope="col">Dt. Criação</th>
                            <th scope="col" class="text-right">Ação</th>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->question }}</td>
                                <td>{{ $item->is_enabled ? 'Sim' : 'Não' }}</td>
                                <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('faq.destroy', $item->id) }}" method="post"
                                                id="form-{{$item->id}}">
                                                @csrf
                                                @method('delete')
                                                @can('faq_view')
                                                <a class="dropdown-item"
                                                    href="{{ route('faq.show', $item) }}">Visualizar</a>
                                                @endcan
                                                @can('faq_edit')
                                                <a class="dropdown-item"
                                                    href="{{ route('faq.edit', $item) }}">Editar</a>
                                                @endcan
                                                @can('sections_delete')
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
                    {{ $data->links() }}
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection
