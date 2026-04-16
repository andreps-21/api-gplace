@extends('layouts.app', ['page' => 'Pacientes', 'pageSlug' => 'customers'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">Cliente</h3>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary">
                            Informações do Cliente
                        </div>
                        <div class="card-body">
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Nome: </strong></p>
                                        <p class="card-text">
                                            {{ $item->name }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>CPF: </strong></p>
                                        <p class="card-text">
                                            {{ nifMask($item->nif) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Email: </strong></p>
                                        <p class="card-text">
                                            {{ $item->email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Telefone: </strong></p>
                                        <p class="card-text">
                                            {{ $item->phone }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Cidade: </strong></p>
                                        <p class="card-text">
                                            {{ $item->city }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Origem: </strong></p>
                                        <p class="card-text">
                                            {{ $item->origin }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Data de Nascimento: </strong></p>
                                        <p class="card-text">
                                            {{ $item->birth_date }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Endereço: </strong></p>
                                        <p class="card-text">
                                            {{ $item->street }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Dt. Criação: </strong></p>
                                        <p class="card-text">
                                            {{ $item->created_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Dt. Atualização: </strong></p>
                                        <p class="card-text">
                                            {{ $item->updated_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Registro Estadual: </strong></p>
                                        <p class="card-text">
                                            {{ $item->state_registration }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Razão Social/Nome: </strong></p>
                                        <p class="card-text">
                                            {{ $item->formal_name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
