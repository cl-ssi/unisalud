<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;


    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'run' => ['required'],
            'password' => ['required'],
        ]);

        /*
        * Limpiar run y quitar el DV
        */
        $credentials['run'] = str_replace('.','',$credentials['run']);
        $credentials['run'] = str_replace('-','',$credentials['run']);
        $credentials['run'] = substr($credentials['run'], 0, -1);

        $user = User::getUserByRun($credentials['run']);

        if($user AND $user->can('Migrar a Neo'))
            {
                session()->flash('info', 
                    'Estimado usuario.<br> Deberá ingresar a la nueva plataforma NeoSalud a través de la siguiente dirección: 
                    <b>https://neo.saludtarapaca.gob.cl/</b> <br>Muchas gracias.');
                return redirect()->route('welcome');
            }

        if($user) {
            unset($credentials['run']);
            $credentials['id'] = $user->id;

            if (Auth::attempt($credentials, $request->remember)) {
                $request->session()->regenerate();

                return redirect()->intended('home');
            }

            return back()->withErrors([
                'run' => 'Clave incorrecta',
            ]);
        }
        else {
            return redirect()->back()->withErrors(['No existe el usuario']);;
        }

    }

    public function logout(){
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        if(env('APP_ENV') == 'local'){
            return redirect()->route('welcome');
        }
        else {
            return redirect()->route('welcome');
        }
        
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
