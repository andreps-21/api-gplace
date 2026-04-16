@extends('layouts.app', ['page' => 'Leads', 'pageSlug' => 'leads'])

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-0">Lead</h3>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {!!Form::open()
                    ->post()
                    ->route('leads.store')
                    ->multipart()
                    !!}
                        @include('leads._forms')
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
