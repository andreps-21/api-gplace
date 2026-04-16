@extends('layouts.app', ['page' => 'FAQ', 'pageSlug' => 'faq'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-0">Faq</h3>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('faq.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Pergunta: </strong></p>
                                    <p class="card-text">
                                        {{ $item->question }}
                                    </p>
                                </div>
                            </div>
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Ativo: </strong></p>
                                    <p class="card-text">
                                        {{ $item->is_enabled ? 'Sim' : 'Não' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Resposta: </strong></p>
                                    <p class="card-text">
                                        {!! $item->answer !!}
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
@endsection

@push('js')
    <script>
        $('.textarea').summernote({
            tabsize: 2,
            lang: "pt-BR",
            toolbar: null,
            editable: false,
        });
    </script>
@endpush
