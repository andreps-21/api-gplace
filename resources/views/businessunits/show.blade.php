@extends('layouts.app', ['page' => 'Unidade', 'pageSlug' => 'businessunit'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">Unidade</h3>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('business-units.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Nome:</strong></p>
                                    <p class="card-text">
                                        {{ $item->name }}
                                    </p>
                                </div>
                            </div>
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Descritivo:</strong></p>
                                    <p class="card-text">
                                        {{ $item->description }}
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
                                    <p><strong>Ativo: </strong></p>
                                    <p class="card-text">
                                        {{ $item->is_enabled ? "Sim" : "Não" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>CEP Início: </strong></p>
                                    <p class="card-text">
                                        {{ $item->zip_code_start }}
                                    </p>
                                </div>
                            </div>
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>CEP Fim: </strong></p>
                                    <p class="card-text">
                                        {{ $item->zip_code_end }}
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
@endsection
