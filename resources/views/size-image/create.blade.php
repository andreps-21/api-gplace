@extends('layouts.app', ['page' => 'Tamanho Imagem', 'pageSlug' => 'size-image'])

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title">Tamanho Mídia</h4>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('size-image.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {!!Form::open()
                    ->post()
                    ->route('size-image.store')
                    ->multipart()
                    !!}
                        @include('size-image._forms')
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
