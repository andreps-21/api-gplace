@extends('layouts.app', ['page' => 'Marca', 'pageSlug' => 'brand'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">Marca</h3>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('brands.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Imagem: </strong></p>
                                    <p class="card-text">
                                        <div class="col-12">
                                            <img id="preview-image"
                                                src="{{asset((isset($item) && $item->image!= null)?'storage/'.$item->image:'images/noimage.png')}}"
                                                class="img-fluid" width="250" height="150" />
                                        </div>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Marca: </strong></p>
                                    <p class="card-text">
                                        {{ $item->name }}
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

                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Contratante: </strong></p>
                                    <p class="card-text">
                                        {{ $item->tenant->people->name }}
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
