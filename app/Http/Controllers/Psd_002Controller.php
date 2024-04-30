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

        $api_key = env('CLI_PASS');     //$api_key = "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";
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
        #dd($_GET);
        $shop = isset($_GET['shop']) ? $_GET['shop'] : '';
        $api_key = env('SHOPIFY_API_KEY');
        $scope = env('SCOPE');
        $redirect_url = env('RE_DIR_URL');

		$fApiUsr=env('API_U');
		$fApiClave=env('API_P');

		$params = $_GET;
		$hmac = isset($_GET['hmac']) ? $_GET['hmac'] : '';
		$code = isset($_GET['code']) ? $_GET['code'] : '';
		$state = isset($_GET['state']) ? $_GET['state'] : '';
		$host = isset($_GET['host']) ? $_GET['host'] : '';
		
        $params = array_diff_key($params, array('hmac' => ''));

		ksort($params);
        
        $computer_hmac = hash_hmac('sha256', http_build_query($params), $api_key);
        //********************************************************************************* */
        
        $params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
        ksort($params); // Sort params lexigraphically
        
        $computed_hmac = hash_hmac('sha256', http_build_query($params), $api_key);
		// Use hmac data to check that the response is from Shopify or not
#        if (hash_equals($hmac, $computed_hmac)) {
            // Set variables for our request
            $query = array(
                "client_id" => $api_key, // Your API key
                "client_secret" => $api_key, // Your app credentials (secret key)
                "code" => $params['code'] // Grab the access key from the URL
            );
         	$psd = $params['shop'];
            // Generate access token URL
            $access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";
        
            // Configure curl client and execute request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $access_token_url);
            curl_setopt($ch, CURLOPT_POST, count($query));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
            $result = curl_exec($ch);
            curl_close($ch);
        
            // Store the access token
            $result = json_decode($result, true);
            dd($result);
            $access_token = $result['access_token'];
        
            // Show the access token (don't do this in production!)
            echo "token devuelto: ";

			echo $hmac.PHP_EOL;
			echo $host.PHP_EOL;
			echo $shop.PHP_EOL;
			echo $state.PHP_EOL;
			echo $fApiUsr.PHP_EOL;
			echo $fApiClave.PHP_EOL;
			echo $code.PHP_EOL;
			echo $access_token;
die();




			//Grabo en la base de datos la informacion de la tienda completa
          $sql = "INSERT INTO `datos`(`hmac`, 
                                         `host`, 
                                         `shop`, 
                                         `state`, 
                                         `fapiusr`, 
                                         `fapiclave`,
                                         `code`,
                                         `token`) 
                    VALUES ('$hmac',
                            '$host',
                            '$shop1',
                            '$state',
                            '$fApiUsr',
                            '$fApiClave',
                            '$code',
                            '$access_token');";
                            
        





    #}


}

}
