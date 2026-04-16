<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }}</title>
        <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('/images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
        integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --input-padding-x: 1.5rem;
            --input-padding-y: 0.75rem;
        }

        .btn-cancel-modal {
            width: 200px;
            border-radius: 20px;
            border-color: #424242;
        }

        .btn-send-modal {
            background: #2f3a8f;
            border-color: #262f73;
            width: 200px;
            border-radius: 20px;
            color: white;
        }

        .btn-send-modal:hover {
            background: #262f73;
            border-color: #2f3a8f;
            color: white;

        }

        .modal-dialog {
            margin-top: 21vh;
        }

        .modal-content {
            border-radius: 1rem;

        }
        .custom-checkbox {
            border-radius: 5.25em;
        }
        body {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 300;
            line-height: 1.5;
            color: #495057;
            text-align: left;
        }

        .login,
        .image {
            min-height: 100vh;
        }

        .bg-image {
            background-image: url('{{ asset('images/backgound.png') }}');
            background-size: cover;
            background-position: center;
        }

        .login-heading {
            font-weight: 300;
        }

        .btn-login {
            font-size: 0.9rem;
            letter-spacing: 0.05rem;
            padding: 0.75rem 1rem;
            border-radius: 2rem;
            background: #2f3a8f;
            border-color: #262f73;
        }

        .btn-login:hover {
            background: #262f73;
            border-color: #2f3a8f;
        }

        .form-label-group {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .form-label-group>input,
        .form-label-group>label {
            padding: var(--input-padding-y) var(--input-padding-x);
            height: auto;
            border-radius: 0.5rem;
        }

        .form-label-group>label {
            position: absolute;
            top: 0;
            left: 0;
            display: block;
            width: 100%;
            margin-bottom: 0;
            /* Override default `<label>` margin */
            line-height: 1.5;
            color: #495057;
            cursor: text;
            /* Match the input under the label */
            border: 1px solid transparent;
            border-radius: .25rem;
            transition: all .1s ease-in-out;
        }

        .form-label-group input::-webkit-input-placeholder {
            color: transparent;
        }

        .form-label-group input:-ms-input-placeholder {
            color: transparent;
        }

        .form-label-group input::-ms-input-placeholder {
            color: transparent;
        }

        .form-label-group input::-moz-placeholder {
            color: transparent;
        }

        .form-label-group input::placeholder {
            color: transparent;
        }

        .form-label-group input:not(:placeholder-shown) {
            padding-top: calc(var(--input-padding-y) + var(--input-padding-y) * (2 / 3));
            padding-bottom: calc(var(--input-padding-y) / 3);
        }

        .form-label-group input:not(:placeholder-shown)~label {
            padding-top: calc(var(--input-padding-y) / 3);
            padding-bottom: calc(var(--input-padding-y) / 3);
            font-size: 12px;
            color: #777;
        }

        /* Fallback for Edge
    -------------------------------------------------- */

        @supports (-ms-ime-align: auto) {
            .form-label-group>label {
                display: none;
            }

            .form-label-group input::-ms-input-placeholder {
                color: #777;
            }
        }

        /* Fallback for IE
    -------------------------------------------------- */

        @media all and (-ms-high-contrast: none),
        (-ms-high-contrast: active) {
            .form-label-group>label {
                display: none;
            }

            .form-label-group input:-ms-input-placeholder {
                color: #777;
            }
        }

    </style>
</head>

<body>

    <form class="form" method="post" action="#">
        @csrf
        <div class="container-fluid">
            <div class="row no-gutter">
                <div class="col-md-8 col-lg-6">
                    <div class="login d-flex align-items-center py-5">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-9 col-lg-8 mx-auto">
                                    <div>
                                        <img src="{{ asset('images/logohorizontal02.png') }}" alt="Logo" class="img-fluid mb-5" width="400px"
                                        style="display: block; margin-left:auto; margin-right:auto;">
                                    </div>
                                    @include('alerts.success')
                                    @include('alerts.error')
                                    <h5 class="login-heading mb-4">Login</h5>
                                    <form class="form" method="post" action="{{ route('login') }}">
                                        @csrf
                                        <div class="form-label-group">
                                            <input type="email" name="email" id="inputEmail"
                                                class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                                placeholder="" value="{{ old('email') }}" required autofocus>
                                            <label for="inputEmail">Digite seu E-mail</label>
                                            @include('alerts.feedback', ['field' => 'email'])
                                        </div>

                                        <div class="input-group mb-3 form-label-group">
                                                <input type="password" id="pass" name="password"
                                                    class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                                    placeholder="Senha" required>
                                                <label for="pass">Digite sua Senha</label>
                                            <div class="input-group-append">
                                                <span class="input-group-text olho btn btn-default" id="olho">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
                                                        <path
                                                            d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
                                                    </svg>
                                                </span>
                                                @include('alerts.feedback', ['field' => 'password'])
                                            </div>
                                        </div>
                                        <div class="form-group form-check">
                                            <input type="checkbox" class="form-check-input" id="lembrar" name="remember">
                                            <label class="form-check-label" for="lembrar">Memorizar</label>
                                        </div>
                                        <button
                                            class="btn btn-lg btn-primary btn-block btn-login text-uppercase font-weight-bold mb-2" type="submit">Entrar</button>
                                        <div class="text-center">
                                            <a class="small" id="openEmailModal" style="font-size: 16px; cursor: pointer;">
                                                Esqueci minha senha</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-none d-md-flex col-md-4 col-lg-6 bg-image"></div>
            </div>
        </div>
    </form>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous">
    </script>

    <script>
        document.getElementById('olho').addEventListener('mousedown', function() {
            if (document.getElementById('pass').type == 'password') {
                document.getElementById('pass').type = 'text';
            } else if (document.getElementById('pass').type == 'text') {
                document.getElementById('pass').type = 'password';
            }
        });
        $(document).ready(function() {
            $("#openEmailModal").click(function() {
                $("#emailmodal").modal("show");
            });
        });
    </script>
    @include('auth.passwords.modals.email')
    @include('auth.passwords.modals.code')
    @include('auth.passwords.modals.password')
</body>
