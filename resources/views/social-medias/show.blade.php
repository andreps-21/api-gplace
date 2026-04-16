@extends('layouts.app', ['page' => 'Redes Sociais', 'pageSlug' => 'ocial-medias'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h4 class="card-title">Redes Sociais</h4>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('social-medias.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="container">
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Icone: </strong></p>
                                    <p class="card-text">
                                        <div class="col-12">
                                            <img id="preview-image"
                                                src="{{asset((isset($item) && $item->icon!= null)?'storage/'.$item->icon:'images/noimage.png')}}"
                                                class="img-fluid" width="250" height="150" />
                                            <br />

                                            <input type="file" name="icon" id="image"
                                                class="d-none form-control @if($errors->has('image')) is-invalid @endif" accept="image/*">
                                            @if($errors->has('image'))
                                            <div class="invalid-feedback">{{$errors->first('image')}}</div>
                                            @endif
                                        </div>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card-deck">
                            <div class="card m-2 shadow-sm">
                                <div class="card-body">
                                    <p><strong>Descrição: </strong></p>
                                    <p class="card-text">
                                        {{ $item->description}}
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


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
