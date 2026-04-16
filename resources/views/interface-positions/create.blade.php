@extends('layouts.app', ['page' => 'Posição na Interface', 'pageSlug' => 'interface-positions'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="card-title">Criar Posição na Interface</h4>
                    </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('interface-positions.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {!!Form::open()
                    ->post()
                    ->route('interface-positions.store')!!}
                    <div class="pl-lg-4">
                        @include('interface-positions._forms')
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
