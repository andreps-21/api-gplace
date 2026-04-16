@extends('layouts.app', ['page' => 'Termos e Condições', 'pageSlug' => 'terms'])

@section('content')
<div class="container-fluid mt--7">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <h3 class="mb-0">Termos e Condições</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('alerts.success')
                    @include('alerts.error')
                    {!!Form::open()
                    ->post()
                    ->route('terms.store')
                    ->multipart()!!}
                    <div class="pl-lg-4">
                        @include('terms._forms')
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
