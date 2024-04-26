<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dato;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class Psd_002Controller extends Controller
{
#    public function index($param1, $param2, $param3, $param4)
    public function index()
    {

        $shared_secret = env('CLI_PASS');     //$shared_secret = "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";
        $shop1 = env('SHOP_TEST');
        $api_key = env('CLI_ID');
        $scope = env('SCOPE');
        $redirect_url = env('REDIRECT_URL');

        $fApiUsr = env('API_U');
        $fApiClave = env('API_P');


        #$params = $_GET;
        #$shop = isset($param1) ? $param1 : '';
        #$api_key = isset($param2) ? $param2 : '';
        #$scope = isset($param3) ? $param3 : '';
        #$redirect_url = isset($param4) ? $param4 : '';
/*        $shop = Session::get('parametro1');
        $api_key = Session::get('parametro2');
        $scope = Session::get('parametro3');
        $redirect_url = Session::get('parametro4');*/

#dd($redirect_url);
        $install = "https://" .$shop1 ."/admin/oauth/authorize?client_id=" .$api_key ."&scope=" .$scope."&redirect_uri=".$redirect_url;
        #Session::put('install', $install);
        #header("Location: " .$install);
        // Hacer la solicitud a la ruta externa
        $response = Http::get('http://www.google.com');
        dd($response);
        dd($install);
        dd($redirect_url);

        // Obtener los datos de la respuesta
        $datos = $response->json();
        // Llamar al otro método del mismo controlador y pasarle los datos obtenidos
        #$this->webhook($datos);




        #return Redirect()->route($install);

    }

    public function webhook($param1, $param2, $param3, $param4)
    {
        echo 'asdfasfasdfaasdf';
        dd($param1.PHP_EOL.$param2.PHP_EOL.$param3.PHP_EOL.$param4);
        $shop = $_SESSION['shop'];

        $shop = Session::get('shop');
        $api_key = env('SHOPIFY_API_KEY');
        $scope = env('SCOPE');
        $redirect_url = env('RE_DIR_URL');

        $hmac = Session::get('hmac');
        $code = Session::get('code');
        $state = Session::get('state');
        $host = Session::get('host');

        dd($shop);

        #        $install = Session::get('install');
        
    }

    public function segundowebhook()
    {
        dd('ddddddddddddddddddddddddddddddddddd'.$_GET['hmac']);
    }


}
