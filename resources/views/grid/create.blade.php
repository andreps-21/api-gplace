@extends('layouts.app', ['page' => 'Grid', 'pageSlug' => 'grid'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card ">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="card-title">Criar Grade de Variação</h4>
                    </div>
                        <div class="col-4 text-right">
                            <a href="{{ route('grid.index') }}" class="btn btn-sm btn-primary">Voltar</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {!!Form::open()
                    ->post()
                    ->route('grid.store')!!}
                    <div class="pl-lg-4">
                        @include('grid._forms')
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
