@extends('layouts.app', ['page' => 'Configurações', 'pageSlug' => 'settings'])

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title">Configurações</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('alerts.success')
                    @include('alerts.error')
                    {!!Form::open()
                    ->fill($settings)
                    ->put()
                    ->route('settings.update')
                    ->multipart()
                    !!}
                        @include('settings._forms')
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
