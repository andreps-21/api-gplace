@extends('layouts.app', ['page' => 'Banners', 'pageSlug' => 'banners'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="card-title">Mídias/Anúncios</h4>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('banners.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-deck">
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Imagem: </strong></p>
                                <br>
                                <img id="preview-image"
                                    src="{{asset((isset($item) && $item->filename != null) ? 'storage/' . $item->filename : 'images/noimage.png')}}"
                                    class="img-fluid" width="250" height="150" />
                                <br />
                            </div>
                        </div>
                    </div>
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
                                <p><strong>Tipo de mídia: </strong></p>
                                <p class="card-text">
                                    {{ $item->types($item->type) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-deck">
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Posição: </strong></p>
                                <p class="card-text">
                                    {{ $item->sizeImages->implode('name' , ', ') }}
                                </p>
                            </div>
                        </div>
                        <div class="card m-2 shadow-sm">
                            <div class="card-body">
                                <p><strong>Sequência: </strong></p>
                                <p class="card-text">
                                    {{ $item->sequence }}
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
                                <p><strong>Url/Script: </strong></p>
                                <p class="card-text">
                                    {{ $item->url }}
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
@endsection
