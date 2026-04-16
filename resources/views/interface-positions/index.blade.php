@extends('layouts.app', ['page' => 'Posição na Interface', 'pageSlug' => 'interface-positions'])

@push('css')
<style>
    .dataTables_length,.dataTables_filter ,.dataTables_info,.dataTables_lengthm,.dataTables_paginate
    {
      display: none;
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
                        <h4 class="card-title">Posição na Interface</h4>
                    </div>
                    @can('grid_create')
                    <div class="col-4 text-right">
                        <a href="{{ route('interface-positions.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                    </div>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @include('alerts.success')
                @include('alerts.error')

                <div class="">
                    <table class="table tablesorter table-striped" id="tree-table">
                        <caption>N. Registros: {{ $data->total() }}</caption>
                        <thead class=" text-primary">
                            <th scope="col" width="90px"><span> ID Posição </span></th>             
                            <th scope="col" style="padding-left: 300px" ><span> Nome Posição</span></th>
                            <th scope="col" style="padding-left: 300px">Ativo</th>
                            <th scope="col" class="text-right">Ação</th>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->id_position }}</td>
                                <td style="padding-left: 300px">{{ $item->position_name }}</td>
                                <td style="padding-left: 300px">{{ $item->is_enabled ? 'Sim' : 'Não' }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light"
                                            href="#"
                                            role="button"
                                            data-toggle="dropdown"
                                            aria-haspopup="true"
                                            aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('interface-positions.destroy', $item->id) }}"
                                            method="post"
                                            id="form-{{ $item->id }}">
                                            @csrf
                                            @method('delete')
                                            @can('interface-positions_view')
                                                <a class="dropdown-item"
                                                href="{{ route('interface-positions.show', $item) }}">Visualizar</a>
                                            @endcan
                                            @can('interface-positions_edit')
                                                <a class="dropdown-item"
                                                href="{{ route('interface-positions.edit', $item) }}">Editar</a>
                                            @endcan
                                            @can('interface-positions_delete')
                                                <button type="button"
                                                class="dropdown-item btn-delete">
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
                                <td colspan="20"
                                    style="text-align: center; font-size: 1.1em;">
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

@push('js')
    <script>


    function ordenarTabelaNumerica() {
      $('#tree-table').DataTable({
        order: [[0, 'asc']],
        order: [[1, 'asc']],
        columnDefs: [
          { type: 'num', targets: 0 },
          { targets: [2,3], orderable: false }
        ]
      });
    }

    $(document).ready(function() {    
      ordenarTabelaNumerica();  
    });


    </script>
@endpush