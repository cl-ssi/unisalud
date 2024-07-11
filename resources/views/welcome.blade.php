<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sitio web del Servicio de Salud Tarapacá">
    <meta name="author" content="Servicio de Salud Tarapacá">
    <title>{{ config('app.name') }}</title>

    <!-- Styles -->
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
        integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l"
        crossorigin="anonymous">
    <link href="{{ asset('css/cu.min.css') }}" rel="stylesheet">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    @production
    <link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico">
    @else
    <link rel="icon" type="image/vnd.microsoft.icon" href="favicon-local.ico">
    @endproduction


    <meta name="theme-color" content="#563d7c">

    <style>
        h1 {
            font-family: Sans-serif;
            font-weight: 200;
            color: #006fb3;
            font-size: 2.4rem;
        }
        .gb_azul {
            color: #006fb3;
        }
        .gb_rojo {
            color: #fe6565;
        }
        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .bg-nav-gobierno {
            @switch(env('APP_ENV'))
                @case('local') background-color: rgb(73, 17, 82) !important; @break
                @case('testing') background-color: rgb(2, 82, 0) !important; @break
            @endswitch
        }
    </style>
</head>

<body>
    <div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom shadow-sm container bg-nav-gobierno">
        <h5 class="my-0 mr-md-auto font-weight-normal"> <img src="{{ asset('images/gob-header.svg') }}" alt="Logo del gobierno de chile"> </h5>
        <nav class="my-2 my-md-0 mr-md-3">
            <a class="p-2 text-dark" href="https://{{ env('APP_SS_WEBSITE') }}">Web Servicio de Salud</a>
        </nav>
    </div>

    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center container">
        <h1 class="display-5 mb-3">{{ env('APP_NAME') }}</h1>
        <div class="d-flex justify-content-center">
            <table class="align-self-center">
                <tr>
                    <td style="background-color: #006fb3;" width="300" height="6"></td>
                    <td style="background-color: #fe6565;" width="300" height="6"></td>
                </tr>
            </table>
        </div>
        <p class="text-muted mt-4">Bienvenido al portal para la comunidad del Servicio de Salud de Tarapacá.</p>

    </div>

    <div class="container">
        <div class="card-deck mb-3">

            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="my-0 font-weight-normal text-center">Ingreso al portal</h4>
                </div>
                <div class="card-body">

                    <h6 class="mb-3 text-center">Utilizando tu Clave Única</h6>

                    <div class="row justify-content-center">
                        <!-- Código para visualizar botón oficial iniciar sesión con ClaveÚnica-->
                        <a class="btn-cu btn-m btn-color-estandar text-center" href="{{ route('socialite.auth.redirect','claveunica') }}"
                            title="Este es el botón Iniciar sesión de ClaveÚnica">
                            <span class="cl-claveunica"></span>
                            <span class="texto">Iniciar sesión</span>
                        </a>
                        <!--./ fin botón-->
                    </div>

                    <hr>

                    <h6 class="mb-3 text-center">Utilizando tu cuenta del sistema</h6>

                    <div class="row justify-content-center">
                        <!-- Código para visualizar botón oficial iniciar sesión con ClaveÚnica-->
                        <a class="btn-cu btn-m btn-secondary text-center" href="{{ route('filament.admin.auth.login') }}"
                            title="Este es el botón Iniciar sesión de ClaveÚnica">
                            <span class="texto">Iniciar sesión</span>
                        </a>
                        <!--./ fin botón-->
                    </div>

                </div>
            </div>
            
            <div class="card shadow-sm text-center">
                <div class="card-header">
                    <h4 class="my-0 font-weight-normal">Ciudadanía</h4>
                </div>
                <div class="card-body">
                <p>Bienvenido al portal de Salud de la Región de Tarapacá. Acá encontrarás la información 
                que esté disponible para ti y la podrás consultar en línea utilizando tu clave única del estado, 
                haciendo click en el botón al lado izquierdo.</p>
                </div>
            </div>



            <div class="card shadow-md">
                <div class="card-header">
                    <h4 class="my-0 font-weight-normal">
                        <a href="https://portal.saludtarapaca.gob.cl" class="btn btn-secondary btn-block">Portal de sistemas</a>
                    </h4>
                </div>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <a href="https://i.saludtarapaca.gob.cl" 
                            class="btn btn-outline-info btn-block">iOnline</a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://i.saludtarapaca.gob.cl/login/external" 
                            class="btn btn-outline-info btn-block">Externos</a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://neo.saludtarapaca.gob.cl" 
                            class="btn btn-outline-info btn-block">NeoSalud</a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://uni.saludtarapaca.gob.cl" 
                            class="btn btn-info btn-block disabled">UniSalud</a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://esmeralda.saludtarapaca.gob.cl" 
                            class="btn btn-outline-info btn-block">Esmeralda</a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://siremx.saludtarapaca.gob.cl" 
                            class="btn btn-outline-info btn-block">Siremx</a>
                    </li>

                </ul>
            </div>


        </div>

        <footer class="pt-4 my-md-5 pt-md-5 border-top">
            <div class="row">
                <div class="col-3 col-md-3">
                    <img class="mb-2" src="{{ asset('images/logo_sst_100px.png') }}" alt="Logo Servicio de Salud Tarapacá">
                </div>
                <div class="col-1 col-md-6">

                </div>
                <div class="col-7 col-md-3">
                    <h5>Desarrollado por</h5>
                    <ul class="list-unstyled text-small">
                        <li>Departamento TIC del SST</li>
                        <li><a class="text-muted" href="mailto:sistemas.sst@redsalud.gob.cl">sistemas.sst@redsalud.gob.cl</a></li>
                        <small class="d-block mb-3 text-muted">{{ now()->year }}</small>
                    </ul>
                </div>
            </div>
        </footer>
    </div>


</body>

</html>
