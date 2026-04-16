@extends('layouts.app', ['page' => 'Redes Sociais', 'pageSlug' => 'social-medias'])

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
                    {!!Form::open()->fill($item)
                    ->put()
                    ->route('social-medias.update', [$item->id])
                    ->multipart()
                    !!}
                    <div class="pl-lg-4">
                        @include('social-medias._forms')
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
