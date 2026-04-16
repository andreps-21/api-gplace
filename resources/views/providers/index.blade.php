@extends('layouts.app', ['page' => 'Fornecedores', 'pageSlug' => 'providers'])

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card ">
                <div class="card-header">
                    <div class="row">
                        <div class="col-8">
                            <h4 class="card-title">Fornecedores</h4>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('providers.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('alerts.success')
                    @include('alerts.error')
                    {!! Form::open()->fill(request()->all())->get() !!}
                    <div class="row">
                        <div class="col-md-3">
                            {!! Form::text('search', 'Pesquisar por nome ou CNPJ/CPF') !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::select('status', 'Status', [null => 'Selecione...'] + \App\Models\Provider::opStatus()) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::date('start_date', 'Dt. Inicio') !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::date('end_date', 'Dt. Fim') !!}
                        </div>
                        <div style="margin-top: -20px;" class="col-md-12 text-right">
                            <br>
                            <button class="btn btn-sm  btn-primary" style="font-size: 9px;" type="submit"><svg
                                    xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="currentColor"
                                    class="bi bi-funnel-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z" />
                                </svg> Filtrar</button>
                            <a id="clear-filter" style="font-size: 9px;" class="btn btn-sm btn-danger"
                                href="{{ route('providers.index') }}"><svg xmlns="http://www.w3.org/2000/svg" width="9"
                                    height="9" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z" />
                                </svg> Limpar</a>
                        </div>
                    </div>

                    {!! Form::close() !!}
                    <div class="">
                        <table class="table tablesorter table-striped" id="">
                            <thead class=" text-primary">
                                <th scope="col">Nome Fantasia/Apelido</th>
                                <th scope="col">CNPJ/CPF</th>
                                <th scope="col">Telefone</th>
                                <th scope="col">Tipo</th>
                                <th scope="col">status</th>
                                <th scope="col" class="text-right">Ação</th>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->nif }}</td>
                                        <td>{{ $item->phone }}</td>
                                        <td>{{ $item->types($item->type) }}</td>
                                        <td>{{ $item->opStatus($item->status) }}</td>
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    <form action="{{ route('providers.destroy', $item->id) }}"
                                                        method="post" id="form-{{ $item->id }}">
                                                        @csrf
                                                        @method('delete')
                                                        <a class="dropdown-item"
                                                            href="{{ route('providers.show', $item) }}">Visualizar</a>
                                                        <a class="dropdown-item"
                                                            href="{{ route('providers.edit', $item) }}">Editar</a>
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
                    <nav class="d-flex justify-content-end" aria-label="...">
                        {{ $data->appends(request()->all())->links() }}
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection
