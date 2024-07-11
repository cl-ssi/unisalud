<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClaveUnicaController extends Controller
{
    /** 
     * Está implementada un login alternativo para iOnline
     * Una vez que se migre ya no será necesario
     */
    /** iOnline Borrar el utlimo parametro $route (tambien en el web.php) cuando ya no se ocupe la redireccion */
    public function autenticar(Request $request, $route = null)
    {
        /* Primer paso, redireccionar al login de clave única */
        $url_base       = "https://accounts.claveunica.gob.cl/openid/authorize/";
        $client_id      = env("CLAVEUNICA_CLIENT_ID");

        /** iOnline, descomentar y borrar la otra linea */
        $redirect_uri   = urlencode(env('APP_URL') . "/auth/claveunica/callback?route=$route");
        // $redirect_uri   = urlencode(env('APP_URL')."/claveunica/callback");

        $state          = csrf_token();
        $scope          = 'openid run name';

        $params         = '?client_id=' . $client_id .
            '&redirect_uri=' . $redirect_uri .
            '&scope=' . $scope .
            '&response_type=code' .
            '&state=' . $state;

        return redirect()->to($url_base . $params)->send();
    }

    public function callback(Request $request)
    {
        /* Segundo paso, el usuario ya se autentificó correctamente en CU y retornó a nuestro sistema */
        $code = request()->input('code');
        $state = request()->input('state');
        $route = request()->input('route');


        /* Recepcionamos los siguientes parametros desde CU */
        $url_base       = "https://accounts.claveunica.gob.cl/openid/token/";
        $client_id      = env("CLAVEUNICA_CLIENT_ID");
        $client_secret  = env("CLAVEUNICA_CLIENT_SECRET");
        $redirect_uri   = env("CLAVEUNICA_REDIRECT_URI");

        $scope = 'openid+run+name';

        $response = Http::asForm()->post($url_base, [
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $redirect_uri,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'state'         => $state,
        ]);


        /** iOnline dejar solo el ultimo return */
        $responseData = json_decode($response);

        dd($code, $state, $route, $redirect_uri, $response, $responseData, $response->body());

        if ($responseData === null || !property_exists($responseData, 'access_token')) {
            // $responseData = json_decode($response->body());
            // {
            //     "error": "invalid_grant"
            //     "error_description": "The provided authorization grant or refresh token is invalid, expired, revoked, does not match the redirection URI used in the authorization request, or was issued to another client"
            // }
            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['msg' => 'No se pudo iniciar Sesión con Clave Única']);
        }

        if (isset($route)) {
            $redirect = str_ireplace('uni.', $route . '.', parse_url(env('APP_URL'), PHP_URL_HOST));
            // logger()->channel('slack')->info('Logeo por CU desde unisalud a iOnline');

            return redirect('https://' . $redirect . '/claveunica/login/' . $responseData->access_token);
        } else {
            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['msg' => 'No existe ruta para redireccionar']);
        }
    }

    // public function getUserInfo($access_token)
    // {
    //     /* Paso 3, obtener los datos del usuario en base al $access_token */
    //     $url_base = "https://accounts.claveunica.gob.cl/openid/userinfo/";
    //     $response = Http::withToken($access_token)->post($url_base);

    //     $user_clave_unica = json_decode($response);

    //     /* Registrar los datos del usuario en la BD local */

    //     $user_local = User::query()
    //         ->getByRun($user_clave_unica->RolUnico->numero)
    //         ->first();

    //     if ($user_local) {
    //         /* Actualiza el correo si es que ha cambiado */
    //         if (property_exists($user_clave_unica, 'email')) {
    //             if ($user_local->email != $user_clave_unica->email) {
    //                 $user_local->email = $user_clave_unica->email;
    //                 $user_local->save();
    //             }
    //         }
    //     } else {
    //         $user_local = new User();
    //         $user_local->active = 1;
    //         $user_local->claveunica = true;
    //         $user_local->save();

    //         $identifier = new Identifier();
    //         $identifier->use = 'official';
    //         $identifier->cod_con_identifier_type_id = 1; // RUN
    //         $identifier->value = $user_clave_unica->RolUnico->numero;
    //         $identifier->dv = $user_clave_unica->RolUnico->DV;
    //         $identifier->user_id = $user_local->id;
    //         $identifier->save();

    //         $human_name = new HumanName();
    //         $human_name->use = 'official';
    //         $human_name->text = implode(' ', $user_clave_unica->name->nombres);
    //         $human_name->fathers_family = $user_clave_unica->name->apellidos[0];
    //         $human_name->mothers_family = $user_clave_unica->name->apellidos[1];
    //         $human_name->user_id = $user_local->id;
    //         $human_name->save();

    //         if (property_exists($user_clave_unica, 'email')) {
    //             $contact_point = new ContactPoint();
    //             $contact_point->system = 'email';
    //             $contact_point->use = 'home';
    //             $contact_point->value = $user_clave_unica->email;
    //             $contact_point->user_id = $user_local->id;
    //             $contact_point->save();
    //         }
    //     }

    //     if($user_local AND $user_local->can('Migrar a Neo'))
    //         {
    //             session()->flash('info', 
    //                 'Estimado usuario.<br> Deberá ingresar a la nueva plataforma NeoSalud a través de la siguiente dirección: 
    //                 <b>https://neo.saludtarapaca.gob.cl/</b> <br>Muchas gracias.');
    //             return redirect()->route('welcome');
    //         }

    //     Auth::login($user_local, true);

    //     return redirect()->route('home');


    //     /* CU Entrega los datos del usuario en este formato
    //     [RolUnico] => stdClass Object
    //         (
    //             [DV] => 4
    //             [numero] => 44444444
    //             [tipo] => RUN
    //         )

    //     [sub] => 2594
    //     [name] => stdClass Object
    //         (
    //             [apellidos] => Array
    //                 (
    //                     [0] => Del rio
    //                     [1] => Gonzalez
    //                 )

    //             [nombres] => Array
    //                 (
    //                     [0] => Maria
    //                     [1] => Carmen
    //                     [2] => De los angeles
    //                 )

    //         )
    //     [email] => mcdla@mail.com
    //     */
    // }

    // public function logout()
    // {
    //     /* Nos iremos al cerrar sesión en clave única y luego volvermos a nuestro sistema */
    //     if (env('APP_ENV') == 'local') {
    //         /* Si es ambiente de desarrollo cerramos sólo localmente */
    //         return redirect()->route('logout');
    //     } else {
    //         /** Cerrar sesión clave única */
    //         /* Url para cerrar sesión en clave única */
    //         $url_logout     = "https://accounts.claveunica.gob.cl/api/v1/accounts/app/logout?redirect=";
    //         /* Url para luego cerrar sesión en nuestro sisetema */
    //         $url_redirect   = env('APP_URL') . "/logout";
    //         $url            = $url_logout . urlencode($url_redirect);
    //         return redirect($url);
    //     }
    // }
}
