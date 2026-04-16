@extends('layouts.app', ['page' => 'Lojas', 'pageSlug' => 'Lojas'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-0">Criar Loja</h3>
                        </div>
                        <div class="ml-auto mr-3">
                            <a href="{{ route('stores.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {!!Form::open()
                    ->post()
                    ->route('stores.store')
                    ->multipart()!!}
                    <div class="pl-lg-4">
                        @include('stores._forms')
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
