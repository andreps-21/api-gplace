<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
        integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
        integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w=="
        crossorigin="anonymous" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --input-padding-x: 1.5rem;
            --input-padding-y: 0.75rem;
        }

        body {
            width: 100vw;
            height: 100vh;

            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            text-align: left;
        }

        section {
            width: 100%;
            height: 100%;

            display: flex;
            align-items: center;
            justify-content: center;
        }

        div i {
            font-size: 5rem;
            color: var(--success);
        }
    </style>
</head>
<body>
    <section>
        <div class="text-center">
            <i class="fas fa-check-circle"></i>

            <h1 class="mt-2">Sucesso!</h1>

            <p class="mt-1">Autenticação realizada com sucesso!</p>

            <a class="btn btn-success" href="{{ route('home') }}">
                Continuar
            </a>
        </div>
    </section>
</body>
