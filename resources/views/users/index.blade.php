@extends('layouts.app', ['page' => 'Usuários', 'pageSlug' => 'users'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="card-title">Usuários</h4>
                    </div>
                    @can('users_create')
                    <div class="col-md-4 text-right">
                        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                    </div>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                @include('alerts.success')
                @include('alerts.error')
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-4">
                        {!!Form::text('search', 'Pesquisar')
                        !!}
                    </div>

                    <div class="col-md-4">
                        {!!Form::date('start_date', 'Dt. Criação')
                        !!}
                    </div>
                    <div class="col-md-4 text-right">
                        <br>
                        <button class="btn btn-sm  btn-primary" style="font-size: 9px;" type="submit"><svg
                                xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="currentColor"
                                class="bi bi-funnel-fill" viewBox="0 0 16 16">
                                <path
                                    d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z" />
                            </svg> Filtrar</button>
                        <a id="clear-filter" style="font-size: 9px;" class="btn btn-sm btn-danger"
                            href="{{ route('users.index') }}"><svg xmlns="http://www.w3.org/2000/svg" width="9"
                                height="9" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                <path
                                    d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z" />
                            </svg> Limpar</a>
                    </div>

                </div>
                {!!Form::close()!!}
                <div class="table-responsive-xl">
                    <table class="table table-striped" id="">
                        <caption>N. Registros: {{ $data->total() }}</caption>
                        <thead class=" text-primary">
                            <th scope="col">Nome</th>
                            <th scope="col">CPF/CNPJ</th>
                            <th scope="col">Email</th>
                            <th scope="col">Celular</th>
                            <th scope="col">Dt. Criação</th>
                            <th scope="col">Ação</th>

                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr style="font-size: 12px;">
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->nif }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    @if(isset($item->phone) && !empty($item->phone))
                                        {{ insertMask($item->phone, '(##) #####-####') }}
                                    @endif
                                </td>
                                <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('users.destroy', $item->id) }}" method="post"
                                                id="form-{{$item->id}}">
                                                @csrf
                                                @method('delete')
                                                @can('users_view')
                                                <a class="dropdown-item"
                                                    href="{{ route('users.show', $item) }}">Visualizar</a>
                                                @endcan
                                                @can('users_edit')
                                                <a class="dropdown-item"
                                                    href="{{ route('users.edit', $item) }}">Editar</a>
                                                @endcan
                                                @if(auth()->id() != $item->id)
                                                @can('users_delete')
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
