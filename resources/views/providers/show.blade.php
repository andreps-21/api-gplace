@extends('layouts.app', ['page' => 'Fornecedores', 'pageSlug' => 'providers'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">Fornecedor</h3>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('providers.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary">
                            Informações do Fornecedor
                        </div>
                        <div class="card-body">
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Nome completo/Razão social: </strong></p>
                                        <p class="card-text">
                                            {{ $item->formal_name }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Nome fantasia/Apelido: </strong></p>
                                        <p class="card-text">
                                            {{ $item->name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>CPF/CNPJ: </strong></p>
                                        <p class="card-text">
                                            {{ $item->nif }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Email: </strong></p>
                                        <p class="card-text">
                                            {{ $item->email }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>RG/Inscrição estadual: </strong></p>
                                        <p class="card-text">
                                            {{ $item->state_registration }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Inscrição municipal: </strong></p>
                                        <p class="card-text">
                                            {{ $item->municipal_registration }}
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
                                        <p><strong>Tipo: </strong></p>
                                        <p class="card-text">
                                            {{ $item->types($item->type) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
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
                                        <p><strong>Bairro: </strong></p>
                                        <p class="card-text">
                                            {{ $item->district }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>CEP: </strong></p>
                                        <p class="card-text">
                                            {{ $item->zip_code }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Cidade: </strong></p>
                                        <p class="card-text">
                                            {{ $item->city }}
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
                                <div class="card-body">
                                    <p><strong>Contato Tel.: </strong></p>
                                    <p class="card-text">
                                        {{ $item->contact_phone }}
                                    </p>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Status: </strong></p>
                                        <p class="card-text">
                                            {{ $item->opStatus($item->status) }}
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
                                        <p><strong>Equipamento próprio: </strong></p>
                                        <p class="card-text">
                                            {{ $item->own_equipment ? 'Sim' : 'Não' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Transporte próprio: </strong></p>
                                        <p class="card-text">
                                            {{ $item->own_transport ? 'Sim' : 'Não' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Banco: </strong></p>
                                        <p class="card-text">
                                            {{ $item->bank }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Agência: </strong></p>
                                        <p class="card-text">
                                            {{ $item->agency }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Conta: </strong></p>
                                        <p class="card-text">
                                            {{ $item->account }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Tipo da conta: </strong></p>
                                        <p class="card-text">
                                            {{ $item->account_type ? 'Corrente' : 'Poupança'}}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Observações: </strong></p>
                                        <p class="card-text">
                                            {{ $item->notes }}
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
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
