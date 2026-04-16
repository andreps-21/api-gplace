@extends('layouts.app', ['page' => 'Contratantes', 'pageSlug' => 'tenants'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-0">Contratantes</h3>
                        </div>
                        <div class="ml-auto mr-3">
                            <a href="{{ route('tenants.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
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
                                <p><strong>Razão Social: </strong></p>
                                <p class="card-text">
                                    {{ $item->formal_name }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>CPF/CNPJ: </strong></p>
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
                                <p><strong>Celular: </strong></p>
                                <p class="card-text">
                                    {{ $item->cellphone }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Status: </strong></p>
                                <p class="card-text">
                                    {{ $item->opStatus($item->status) }}
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
                                <p><strong>Endereço: </strong></p>
                                <p class="card-text">
                                    {{ $item->street }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>CEP: </strong></p>
                                <p class="card-text">
                                    {{ $item->zip_code }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-deck">
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Contato: </strong></p>
                                <p class="card-text">
                                    {{ $item->contact }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Contato Telefone: </strong></p>
                                <p class="card-text">
                                    {{ $item->contact_phone }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-deck">
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Dt. Adesão: </strong></p>
                                <p class="card-text">
                                    {{ $item->dt_accession->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Dt. Vigência Assinatura: </strong></p>
                                <p class="card-text">
                                    {{ $item->due_date->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Dia do vencimento: </strong></p>
                                <p class="card-text">
                                    {{ $item->opDueDays($item->due_day) }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Valor: </strong></p>
                                <p class="card-text">
                                    {{ money($item->value) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-deck">
                    <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Assinatura: </strong></p>
                                <p class="card-text">
                                    {{ $item->opSignatures($item->signature) }}
                                </p>
                            </div>
                        </div>
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
