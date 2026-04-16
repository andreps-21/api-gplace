@extends('layouts.app', ['page' => 'Apresentação', 'pageSlug' => 'presentation'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">Apresentação</h3>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('presentations.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Apresentação: </strong></p>
                                    <p class="card-text">
                                        {{ $item->presentation }}
                                    </p>
                                </div>
                            </div>

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Detalhamento: </strong></p>
                                    <p class="card-text">
                                        {{ $item->detailing }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Ativo: </strong></p>
                                    <p class="card-text">
                                        {{ $item->is_enabled ? 'Sim' : 'Não' }}
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
