<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <!-- Scripts -->
        <!-- Bootstrap -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
            integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p"
            crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js"
            integrity="sha384-Atwg2Pkwv9vp0ygtn1JAojH0nYbwNJLPhwyoVbhoPwBhjQPR5VtM2+xf0Uwh9KtT"
            crossorigin="anonymous"></script>

        <script src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" data-mutate-approach="sync"></script>

        <!-- <script src="{{ asset('js/app.js') }}" defer></script> -->
        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">

        <link href="{{ asset('css/nunito.css') }}" rel="stylesheet">
        <!-- Styles -->
        <!-- Bootstrap -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
            integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l"
            crossorigin="anonymous">

        <!-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> -->

        <!-- Bootstrap CSS -->
        <!--link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous"-->

        <!-- Custom styles for this template -->
        <link href="{{ asset('css/ssi.css') }}" rel="stylesheet">

        <!-- Favicons -->
        <link rel="apple-touch-icon" sizes="180x180" href="/icon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/icon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/icon/favicon-16x16.png">
        <link rel="manifest" href="/icon/site.webmanifest">
        @production
        <link rel="icon" type="image/vnd.microsoft.icon" href="/icon/favicon.ico">
        @else
        <link rel="icon" type="image/vnd.microsoft.icon" href="/icon/favicon-local.ico">
        @endproduction

        <!-- programador -->
        @yield('custom_js_head')

        @livewireStyles
    </head>
    <body>

    @livewireScripts
    <nav class="navbar navbar-dark sticky-top bg-ssi flex-md-nowrap p-0 mb-3 shadow ssi-azul">
            <a class="navbar-ssi @production ssi-rojo @else ssi-morado @endproduction col-md-3 col-lg-2 mr-0 px-3" href="{{ route('home') }}">Servicio de Salud</a>
            <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-toggle="collapse" data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <ul class="navbar-nav ml-auto mr-1">
                <li class="nav-item">

                    @if(auth()->check())
                        <a id="navbarDropdown" class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->officialName }} <span class="caret"></span>
                        </a>
                    @endif
                </li>
            </ul>
        </nav>
        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse d-print-none">
                    <div class="sidebar-sticky pt-3">
                        @auth
                        @include('layouts.partials.nav')
                        @endauth
                    </div>
                </nav>
                <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">

                    <h4 class="d-none d-print-block">{{ env('APP_NAME') }}</h4>
                    
                    @include('layouts.partials.errors')
                    @include('layouts.partials.flash_message')
                    
                    @yield('content', $slot ?? '')
                </main>
            </div>
        </div>
        <!--script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script-->

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.9.0/feather.min.js"></script>
        <script>
            feather.replace()
        </script>

        <!-- TODO: #64 eliminar bootstrap 4.6 @sickiqq // programador -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js" integrity="sha512-ubuT8Z88WxezgSqf3RLuNi5lmjstiJcyezx34yIU2gAHonIi27Na7atqzUZCOoY4CExaoFumzOsFQ2Ch+I/HCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.min.js" integrity="sha512-XKa9Hemdy1Ui3KSGgJdgMyYlUg1gM+QhL6cnlyTe2qzMCYm4nAZ1PsVerQzTTXzonUR+dmswHqgJPuwCq1MaAg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        @yield('custom_js')

        @stack('scripts')
        
        <script type="text/javascript">
            $(document).ready(function () {
                $('form').submit(function(){
                    $('input[type=submit]', this).attr('disabled', 'disabled');
                    $('button[type=submit]', this).attr('disabled', 'disabled');
                });

                $(".collapse-menu").on("shown.bs.collapse", function () {
                    localStorage.setItem("coll_" + this.id, true);
                    $('#icon_'+this.id).replaceWith(feather.icons['minus-circle'].toSvg());
                    console.log('SHOW ' + this.id);
                });

                $(".collapse-menu").on("hidden.bs.collapse", function () {
                    localStorage.removeItem("coll_" + this.id);
                    $('#icon_'+this.id).replaceWith(feather.icons['plus-circle'].toSvg());
                    console.log('HIDE ' + this.id);
                });

                $(".collapse-menu").each(function () {
                    console.log('EACH ' + this.id);
                    if (localStorage.getItem("coll_" + this.id) === "true") {
                        $(this).collapse("show");
                    }
                    else {
                        $(this).collapse("hide");
                    }
                });
            });

        </script>

    </body>
</html>
