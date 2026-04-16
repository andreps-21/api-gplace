<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('/images/faicon.png') }}">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
        integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w=="
        crossorigin="anonymous" />

    <!-- CSS -->
    <link href="{{ asset('white') }}/css/white-dashboard.css?v=1005" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap4-duallistbox/4.0.2/bootstrap-duallistbox.min.css"
        integrity="sha512-BcFCeKcQ0xb020bsj/ZtHYnUsvPh9jS8PNIdkmtVoWvPJRi2Ds9sFouAUBo0q8Bq0RA/RlIncn6JVYXFIw/iQA=="
        crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"
        integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw=="
        crossorigin="anonymous" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css"
        integrity="sha512-kq3FES+RuuGoBW3a9R2ELYKRywUEQv0wvPTItv3DSGqjpbNtGWVdvT8qwdKkqvPzT93jp8tSF4+oN4IeTEIlQA=="
        crossorigin="anonymous" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap4-duallistbox/4.0.2/bootstrap-duallistbox.min.css"
        integrity="sha512-BcFCeKcQ0xb020bsj/ZtHYnUsvPh9jS8PNIdkmtVoWvPJRi2Ds9sFouAUBo0q8Bq0RA/RlIncn6JVYXFIw/iQA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{ asset('css/style.css') }}?version=1005" rel="stylesheet" />
    <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@dashboardcode/bsmultiselect@1.1.18/dist/css/BsMultiSelect.bs4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap4-tagsinput@4.1.3/tagsinput.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    @stack('css')
</head>

<body class="white-content {{ $class ?? '' }}">
    <input type="hidden" id="baseurl" name="baseurl" value="{{ config('app.url') }}" />
    @auth()
    <div class="wrapper">
        @include('layouts.navbars.sidebar')
        <div class="main-panel">
            @include('layouts.navbars.navbar')

            <div class="content">
                @yield('content')
            </div>

            @include('layouts.footer')
        </div>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    @else
    <div class="wrapper wrapper-full-page">
        <div class="full-page {{ $contentClass ?? '' }}">
            <div class="content">
                <div class="container">
                    @yield('content')
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
    @endauth
    <div class="modal-loading"></div>
    <script src="{{ asset('white') }}/js/core/jquery.min.js"></script>
    <script src="{{ asset('white') }}/js/core/popper.min.js"></script>
    <script src="{{ asset('white') }}/js/core/bootstrap.min.js"></script>
    <script src="{{ asset('white') }}/js/white-dashboard.min.js?v=1.0.0"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"
        integrity="sha512-Rdk63VC+1UYzGSgd3u2iadi0joUrcwX0IWp2rTh6KXFoAmgOjRS99Vynz1lJPT8dLjvo6JZOqpAHJyfCEZ5KoA=="
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"
        integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A=="
        crossorigin="anonymous"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/i18n/pt-BR.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/pt-BR.min.js"
        integrity="sha512-H1yBoUnrE7X+NeWpeZvBuy2RvrbvLEAEjX/Mu8L2ggUBja62g1z49fAboGidE5YEQyIVMCWJC9krY4/KEqkgag=="
        crossorigin="anonymous"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap4-duallistbox/4.0.2/jquery.bootstrap-duallistbox.min.js"
        integrity="sha512-l/BJWUlogVoiA2Pxj3amAx2N7EW9Kv6ReWFKyJ2n6w7jAQsjXEyki2oEVsE6PuNluzS7MvlZoUydGrHMIg33lw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/@dashboardcode/bsmultiselect@1.1.18/dist/js/BsMultiSelect.bs4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="{{ asset('js/main.js') }}?version=1015"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js" integrity="sha512-9UR1ynHntZdqHnwXKTaOm1s6V9fExqejKvg5XMawEMToW4sSw+3jtLrYfZPijvnwnnE8Uol1O9BcAskoxgec+g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    @stack('js')
</body>

</html>
