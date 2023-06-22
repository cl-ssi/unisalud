<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;

class TestController extends Controller
{
    public function sendIp()
    {

        $client = new Client(['base_uri' => "https://i.saludiquique.cl/" ]);
        $headers = array();
        $response = $client->request('GET', "test-getip", $headers);
        echo "get content: ";
        return $response->getBody();
    }

    /**
    * Test CU
    */
    public function cuTest(Request $request)
    {
        /* Recepcionamos los siguientes parametros desde CU */
        $code           = $request->input('code');
        $state          = $request->input('state');

        $url_base       = "https://accounts.claveunica.gob.cl/openid/token/";
        $client_id      = env("CLAVEUNICA_CLIENT_ID");
        $client_secret  = env("CLAVEUNICA_SECRET_ID");
        $redirect_uri   = urlencode(env('APP_URL') . "/claveunica/callback");

        $scope = 'openid+run+name';

        $response = Http::asForm()->post($url_base, [
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $redirect_uri,
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'state'         => $state,
        ]);

        $responseData = json_decode($response);

        logger()->info($response->successful());
        logger()->info($response->failed());
        dd($response);
    }
}
