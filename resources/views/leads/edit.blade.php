@extends('layouts.app', ['page' => 'Leads', 'pageSlug' => 'leads'])

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-xl-12 order-xl-1">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="card-title">Leads</h4>
                        </div>
                        <div class="col-md-4 text-right">
                            <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {!!Form::open()
                    ->fill($item)
                    ->put()
                    ->route('leads.update', [$item->id])
                    ->multipart()
                    !!}
                    <div class="pl-lg-4">
                        @include('leads._forms')
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
