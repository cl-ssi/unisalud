<?php

namespace App\Http\Controllers;

use App\Models\ContactPoint;
use App\Models\HumanName;
use App\Models\Identifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ClaveUnicaController extends Controller
{
    public function autenticar(Request $request, $redirect = null){
        /* Primer paso, redireccionar al login de clave única */
        $url_base       = "https://accounts.claveunica.gob.cl/openid/authorize/";
        $client_id      = env("CLAVEUNICA_CLIENT_ID");
        $redirect_uri   = urlencode(env("CLAVEUNICA_CALLBACK"));

        if($redirect)
        {
            $redirect_uri .= '?redirect=neosalud';
        }

        /* Si se quiere agregar un $redirect tiene que tener formato: 
            /claveunica/redirect/{ruta.a.redirigir} 
            NO PUEDE TENER "/" la ruta.a.redirigir 
        */
        $state      = csrf_token();
        $scope      = 'openid run name';

        $params     = '?client_id='.$client_id.
                      '&redirect_uri='.$redirect_uri.
                      '&scope='.$scope.
                      '&response_type=code'.
                      '&state='.$state;

        return redirect()->to($url_base.$params)->send();
    }

    public function callback(Request $request) {
        /* Segundo Paso: enviar credenciales de clave única */
        $code       = $request->input('code');
        $state      = $request->input('state');
        $redirect   = $request->input('redirect');
        // $redirect   = null;

        /* Si incluye redirect, el state es más largo que 40 */
        // if(strlen($state) > 40) {
        //     $redirect = substr($state, 40);
        //     $state    = substr($state, 0, 40);
        // }

        /* Validar que el state sea el mismo que le enviamos nosotros */
        //if($state == csrf_token()) { 
            $url_base       = "https://accounts.claveunica.gob.cl/openid/token/";
            $client_id      = env("CLAVEUNICA_CLIENT_ID");
            $client_secret  = env("CLAVEUNICA_SECRET_ID");
            $redirect_uri   = urlencode(env("CLAVEUNICA_CALLBACK"));

            $response = Http::asForm()->post($url_base, [
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => $redirect_uri,
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'state'         => $state,
            ]);

            return $this->getUserInfo(json_decode($response)->access_token, $redirect);
        //}
        // else {
        //     echo 'El state: '.$state.' enviado por clave única es distinto al local:'.csrf_token();
        // }
    }

    public function getUserInfo($access_token, $redirect = null) {
        /* Tercer Paso, obtener los datos de usuario  */
        $url_base = "https://www.claveunica.gob.cl/openid/userinfo";
        $response = Http::withToken($access_token)->post($url_base);

        $user_clave_unica = json_decode($response);

        /* Registrar los datos del usuario en la BD local */
        // $user_local = User::find($user_clave_unica->RolUnico->numero);

        $user_local = User::query()
                        ->getByRun($user_clave_unica->RolUnico->numero)
                        ->first();

        if($user_local) {
            /* Actualiza el correo si es que ha cambiado */
            if(property_exists($user_clave_unica,'email')) {
                if($user_local->email != $user_clave_unica->email) {
                    $user_local->email = $user_clave_unica->email;
                    $user_local->save();
                }
            }
        } 
        else {
            $user_local = new User();
            $user_local->active = 1;
            $user_local->claveunica = true;
            $user_local->save();

            $identifier = new Identifier();
            $identifier->use = 'official';
            $identifier->cod_con_identifier_type_id = 1; // RUN
            $identifier->value = $user_clave_unica->RolUnico->numero;
            $identifier->dv = $user_clave_unica->RolUnico->DV;
            $identifier->user_id = $user_local->id;
            $identifier->save();

            $human_name = new HumanName();
            $human_name->use = 'official';
            $human_name->text = implode(' ', $user_clave_unica->name->nombres);
            $human_name->fathers_family = $user_clave_unica->name->apellidos[0];
            $human_name->mothers_family = $user_clave_unica->name->apellidos[1];
            $human_name->user_id = $user_local->id;
            $human_name->save();

            if(property_exists($user_clave_unica,'email')) { 
                $contact_point = new ContactPoint();
                $contact_point->system = 'email'; 
                $contact_point->use = 'home';
                $contact_point->value = $user_clave_unica->email; 
                $contact_point->user_id = $user_local->id;
                $contact_point->save();
            }
        }

        Auth::login($user_local, true);
        
        /* Si tiene una redirección o de lo contrario se va al home */
        if($redirect) {
            if($redirect == 'neosalud')
            {
                return redirect()->url('https://neo.saludtarapaca.org/login/claveunica/'.$access_token);
            }
            $route = $redirect;
        }
        else {
            $route = 'home';
        }

        return redirect()->route($route);
        
            
        /* CU Entrega los datos del usuario en este formato
        [RolUnico] => stdClass Object
            (
                [DV] => 4
                [numero] => 44444444
                [tipo] => RUN
            )

        [sub] => 2594
        [name] => stdClass Object
            (
                [apellidos] => Array
                    (
                        [0] => Del rio
                        [1] => Gonzalez
                    )

                [nombres] => Array
                    (
                        [0] => Maria
                        [1] => Carmen
                        [2] => De los angeles
                    )

            )
        [email] => mcdla@mail.com
        */
    }

    public function logout() {
        
        if(env('APP_ENV') == 'local'){
            return redirect()->route('logout');
        }else{
            $url_logout = "https://accounts.claveunica.gob.cl/api/v1/accounts/app/logout?redirect=";
            $url_redirect = "https://www.saludiquique.app/logout";
            $url = $url_logout.urlencode($url_redirect);
        }        

        return redirect()->to($url)->send();
    }

}
