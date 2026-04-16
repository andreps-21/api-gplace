@extends('layouts.app', ['page' => 'Contratantes', 'pageSlug' => 'Contratantes'])

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-5">
                        <h4 class="card-title">Contratantes</h4>
                    </div>
                    <div class="ml-auto mr3 text-right no-print">
                        <a class="fas fa-print btn btn-info btn-sm " target="_blank"
                            href=""></a>
                        <a class="btn btn-sm btn-primary" target="_blank"
                            href="">Relatório Geral</a>
                        <a href="{{ route('tenants.create') }}" class="btn btn-sm btn-primary">Adicionar Novo</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @include('alerts.success')
                @include('alerts.error')
                {!!Form::open()->fill(request()->all())
                ->get()                !!}
                    <div class="row">
                        <div class="col-md-3">
                            {!!Form::text('search','Pesquisar por nome ou CPF/CNPJ')!!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::select('status', 'Status', [ null => 'Selecione...'] + \App\Models\Tenant::opStatus()) !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::select('signature', 'Assinatura', [ null => 'Selecione...'] + \App\Models\Tenant::opSignatures()) !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::select('signature', 'Mês Vigência', [ null => 'Selecione...'] + \App\Models\Tenant::opMonth()) !!}
                        </div>
                        <div class="col-md-3">
                            {!!Form::select('due_day', 'Dt. Vencimento', [null => 'Selecione...'] + \App\Models\Tenant::opDueDays())
                            !!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('due_date','Dt. Adesão')->attrs(['class' => 'dat start_date'])!!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('due_date','Dt. Inicio')->attrs(['class' => 'dat start_date'])!!}
                        </div>
                        <div class="col-md-2">
                            {!!Form::date('due_date','Dt. Fim')->attrs(['class' => 'dat start_date'])!!}
                        </div>
                        <div class="col-md-3 text-right">
                        <br>
                        <button class="btn btn-sm  btn-primary" type="submit">Filtrar</button>
                        <a id="clear-filter" class="btn btn-sm  btn-default" href="{{ route('tenants.index') }}"><i
                                class="fa fa-eraser"></i>Limpar</a>
                        </div>
                    </div>
                {!!Form::close()!!}
                    <table class="table table-striped" id="">
                        <thead class=" text-primary">
                            <th scope="col">Nome</th>
                            <th scope="col">CPF/CNPJ</th>
                            <th scope="col">Fone</th>
                            <th scope="col">Status</th>
                            <th scope="col">Dt. Adesão</th>
                            <th scope="col">Dt. Vigência</th>
                            <th scope="col">Valor</th>
                            <th scope="col">Ação</th>

                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ nifMask($item->nif) }}</td>
                                <td>{{ $item->phone }}</td>
                                <td>{{ $item->opStatus($item->status) }}</td>
                                <td>{{ $item->dt_accession->format('d/m/Y') }}</td>
                                <td>{{ $item->due_date->format('d/m/Y') }}</td>
                                <td>{{ $item->value }}</td>
                                <td class="text-right">
                                    <div class="dropdown">
                                        <a class="btn btn-sm btn-icon-only text-light" href="#" role="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                            <form action="{{ route('tenants.destroy', $item->id) }}" method="post"
                                                id="form-{{$item->id}}">
                                                @csrf
                                                @method('delete')
                                                @can('tenants_view')
                                                <a class="dropdown-item"
                                                    href="{{ route('tenants.show', $item) }}">Visualizar</a>
                                                @endcan
                                                @can('tenants_edit')
                                                <a class="dropdown-item"
                                                    href="{{ route('tenants.edit', $item) }}">Editar</a>
                                                @endcan
                                                @can('tenants_delete')
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
