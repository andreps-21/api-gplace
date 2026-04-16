@extends('layouts.app', ['page' => 'Leads', 'pageSlug' => 'users'])

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title">Leads</h4>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-deck">
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>CPF/CNPJ: </strong></p>
                                <p class="card-text">
                                    {{ nifMask($item->nif) }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Nome Completo/Razão Social: </strong></p>
                                <p class="card-text">
                                    {{ $item->formal_name }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Nome Social/Nome Fantasia: </strong></p>
                                <p class="card-text">
                                    {{ $item->name }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-deck">
                        <div class="card m-4 shadow-sm">
                            <div class="card-body">
                                <p><strong>Inscrição Estadual: </strong></p>
                                <p class="card-text">
                                    {{ $item->state_registration }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-4 shadow-sm">
                            <div class="card-body">
                                <p><strong>Inscrição Municipal: </strong></p>
                                <p class="card-text">
                                    {{ $item->city_registration }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-4 shadow-sm">
                            <div class="card-body">
                                <p><strong>Status: </strong></p>
                                <p class="card-text">
                                    {{  \App\Models\Lead::status($item->status) }}
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
                                    {{ phoneMask($item->phone) }}
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
                                <p><strong>CEP: </strong></p>
                                <p class="card-text">
                                    {{ $item->zip_code }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Endereço: </strong></p>
                                <p class="card-text">
                                    {{ $item->address }}
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
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Número: </strong></p>
                                <p class="card-text">
                                    {{ $item->number }}
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
                                <p><strong>Dt. Edição: </strong></p>
                                <p class="card-text">
                                    {{ $item->updated_at->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-deck">
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Observação: </strong></p>
                                <p class="card-text">
                                    {{ $item->observation }}
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
