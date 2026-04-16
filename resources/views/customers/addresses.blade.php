@extends('layouts.app', ['page' => 'Pacientes', 'pageSlug' => 'customers'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">Endereços</h3>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @forelse ($item as $key => $value)
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary">
                            Informações do Endereço {{count($item)>1?$key+1<10?"0".($key+1):$key+1:""}}
                        </div>
                        <div class="card-body">
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-deck m-2">
                                        <div class="card-body shadow-sm">
                                            <p><strong>CEP: </strong></p>
                                            <p class="card-text">
                                                {{ $value->zip_code }}
                                            </p>
                                        </div>
                                        <div class="card-body shadow-sm">
                                            <p><strong>Rua: </strong></p>
                                            <p class="card-text">
                                                {{ $value->street }}
                                            </p>
                                        </div>
                                        <div class="card-body shadow-sm">
                                            <p><strong>Número: </strong></p>
                                            <p class="card-text">
                                                {{ $value->number }}
                                            </p>
                                        </div>
                                        <div class="card-body shadow-sm">
                                            <p><strong>Cidade/UF: </strong></p>
                                            <p class="card-text">
                                                {{ $value->city_uf }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Complemento: </strong></p>
                                        <p class="card-text">
                                            {{ $value->complement }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Distrito: </strong></p>
                                        <p class="card-text">
                                            {{ $value->district }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Referência: </strong></p>
                                        <p class="card-text">
                                            {{ $value->reference }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-deck">
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Dt. Criação: </strong></p>
                                        <p class="card-text">
                                            {{ $value->created_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="card m-2 shadow-sm">
                                    <div class="card-body">
                                        <p><strong>Dt. Atualização: </strong></p>
                                        <p class="card-text">
                                            {{ $value->updated_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                            
                        
                    </div>
                    @empty
                    <div class="card shadow-sm text-center">
                        <div class="card-header bg-secondary p-3">
                            Nenhum endereço cadastrado
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
