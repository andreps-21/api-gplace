@extends('layouts.app', ['page' => __('User Profile'), 'pageSlug' => 'profile'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="title">Editar Perfil</h4>
            </div>
            <form method="post" action="{{ route('profile.update') }}" autocomplete="off">
                <div class="card-body">
                    @csrf
                    @method('put')
                    @include('alerts.success')
                    <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                        <label>Nome</label>
                        <input type="text" name="name"
                            class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                            value="{{ old('name', auth()->user()->name) }}">
                        @include('alerts.feedback', ['field' => 'name'])
                    </div>

                    <div class="form-group{{ $errors->has('email') ? ' has-danger' : '' }}">
                        <label>Email</label>
                        <input type="email" name="email"
                            class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" readonly
                            value="{{ old('email', auth()->user()->email) }}">
                        @include('alerts.feedback', ['field' => 'email'])
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-fill pull-right btn-primary">Salvar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
