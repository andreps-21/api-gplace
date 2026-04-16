@extends('layouts.app', ['page' => 'Cupons', 'pageSlug' => 'coupons'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-0">Cupons</h3>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('coupons.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {!!Form::open()
                    ->post()
                    ->route('coupons.store')
                    ->multipart()!!}
                    <div class="pl-lg-4">
                        @include('coupons._forms')
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
