@extends('layouts.app', ['page' => 'Produtos/Serviços', 'pageSlug' => 'product'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">Produtos</h3>
                        </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {!!Form::open()
                    ->post()
                    ->route('products.store')
                    ->multipart()!!}
                    <div class="pl-lg-4">
                        @include('products._forms')
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
