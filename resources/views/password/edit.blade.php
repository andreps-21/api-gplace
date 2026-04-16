@extends('layouts.app', ['page' => __('User Profile'), 'pageSlug' => 'profile'])

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="title">Alterar Senha</h4>
            </div>
            <form method="post" action="{{ route('change-password.update') }}" autocomplete="off">
                <div class="card-body">
                    @csrf
                    @method('put')
                    @include('alerts.success')
                    <div class="form-group{{ $errors->has('old_password') ? ' has-danger' : '' }}">
                        <label>Senha Atual</label>
                        <input type="password" name="old_password"
                            class="form-control{{ $errors->has('old_password') ? ' is-invalid' : '' }}" required>
                        @include('alerts.feedback', ['field' => 'old_password'])
                    </div>

                    <div class="form-group{{ $errors->has('password') ? ' has-danger' : '' }}">
                        <label>Nova Senha</label>
                        <input type="password" name="password"
                            class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" required>
                        @include('alerts.feedback', ['field' => 'password'])
                    </div>
                    <div class="form-group">
                        <label>Confirmar Senha</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
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
