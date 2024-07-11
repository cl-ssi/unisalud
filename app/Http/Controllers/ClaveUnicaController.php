<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClaveUnicaController extends Controller
{
    public function autenticar(Request $request, $route = null)
    {
        /* Primer paso, redireccionar al login de clave única */
        $url_base     = "https://accounts.claveunica.gob.cl/openid/authorize/";
        $client_id    = env("CLAVEUNICA_CLIENT_ID");
        $redirect_uri = urlencode(env('APP_URL') . "/auth/claveunica/callback?route=$route");
        $state        = csrf_token();
        $scope        = 'openid run name';
        $params       = "?client_id=$client_id&redirect_uri=$redirect_uri&scope=$scope&response_type=code&state=$state";

        return redirect()->to($url_base . $params)->send();
    }
}
