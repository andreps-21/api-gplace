@extends('layouts.app', ['page' => 'Lojas', 'pageSlug' => 'Lojas'])

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-5">
                        <h4 class="card-title">
                            Lojas
                        </h4>
                    </div>
                    <div class="ml-auto mr3 text-right no-print">
                        <a href="{{ route('stores.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @include('alerts.success')
                @include('alerts.error')
                {!!Form::open()->fill(request()->all())->get() !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::text('search', 'Pesquisar por nome ou CNPJ/CPF')!!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('tenant_id', 'Contratantes')->options($tenants->prepend('Selecione', ''), 'name')->attrs(['class' => 'select2'])!!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::select('status', 'Status', [ null => 'Selecione...' ] + \App\Models\Store::opStatus())!!}
                    </div>
                    <div class="col-md-12 text-right">
                        <br>
                        <button class="btn btn-sm  btn-primary" type="submit">Filtrar</button>
                        <a id="clear-filter" class="btn btn-sm  btn-default" href="{{ route('tenants.index') }}"><i
                                class="fa fa-eraser"></i>Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <div>
                    <table class="table table-striped" id="">
                        <thead class="text-primary">
                            <th>Nome</th>
                            <th>Contratante</th>
                            <th>CPF/CNPJ</th>
                            <th>Fone</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->people->name }}</td>
                                <td>{{ $item->tenant->people->name }}</td>
                                <td>{{ nifMask($item->people->nif) }}</td>
                                <td>{{ $item->people->phone }}</td>
                                <td>{{ $item->opStatus($item->status) }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('stores.destroy', $item->id) }}" method="post"
                                                id="form-{{$item->id}}">
                                                @csrf
                                                @method('delete')
                                                @can('stores_view')
                                                <a class="dropdown-item"
                                                    href="{{ route('stores.show', $item) }}">Visualizar</a>
                                                @endcan
                                                @can('stores_edit')
                                                <a class="dropdown-item"
                                                    href="{{ route('stores.edit', $item) }}">Editar</a>
                                                @endcan
                                                @can('stores_delete')
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

@endsection
