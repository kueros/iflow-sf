<?php

namespace App\Http\Controllers;

use App\Models\Shopify;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;

class ShopifyController extends Controller
{
    protected $url_root;

    public function __construct()
    {
        $this->url_root = config('sfenv.url_root');
    }

    public function index()
    {

        $shopifyDatos = Shopify::query()
            ->orderByDesc('id')
            ->get();

        return view('shopify.index')->with('shopifyDatos', $shopifyDatos);
    }

    public function show(int $id)
    {
        return 'Detalle de la Tienda: ' . $id;
    }

    public function create()
    {
        return view('shopify.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'shop' => 'required',
            'fApiUsr' => 'required',
            'fApiClave' => 'required',
        ]);

        Shopify::updateOrInsert(
            ['shop' => $request->input('shop'), 'fApiUsr' => $request->input('fApiUsr')],
            ['fApiClave' => $request->input('fApiClave')]
        );

        return redirect($this->url_root . '/install'); #('.$request.')');
    }

    public function install()
    {
        /* DATOS DEL ENV */
        #Cargo shopifyDatos del env en variables
        $api_key = config('sfenv.api_key');
        $redirect_url =  config('sfenv.redirect_url');
        $scope =  config('sfenv.scope');
        #dd($api_key.PHP_EOL.$redirect_url.PHP_EOL.$scope);

        #Chupo los shopifyDatos del último registro de la tabla
        $shopifyDatos = Shopify::latest()->first();
        $shop = $shopifyDatos->shop;

        $install = "https://" . $shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scope . "&redirect_uri=" . $redirect_url;

        return redirect($install);
    }

    public function segundowebhook()
    {
        // Set variables for our request
        $api_key = env('CLI_ID');
        $shared_secret = env('CLI_PASS');
        $shopifyDatos = Shopify::latest()->first();

        $shop1 = $shopifyDatos->shop;
        $fApiUsr = $shopifyDatos->fapiusr;
        $fApiClave = $shopifyDatos->fapiclave;

        $params = $_GET;
        $hmac = isset($_GET['hmac']) ? $_GET['hmac'] : '';
        $code = isset($_GET['code']) ? $_GET['code'] : '';
        $state = isset($_GET['state']) ? $_GET['state'] : '';
        $host = isset($_GET['host']) ? $_GET['host'] : '';

        $params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
        ksort($params); // Sort params lexigraphically
        #dd($params);
        $computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);

        // Use hmac data to check that the response is from Shopify or not
        if (hash_equals($hmac, $computed_hmac)) {
            // Set variables for our request
            $query = array(
                "client_id" => $api_key, // Your API key
                "client_secret" => $shared_secret, // Your app credentials (secret key)
                "code" => $params['code'] // Grab the access key from the URL
            );
            $psd = $params['shop'];
            // Generate access token URL
            $access_token_url = "https://" . $psd . "/admin/oauth/access_token";

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
            #dd($result);
            $access_token = $result['access_token'];

            // Show the access token (don't do this in production!)
            echo "token devuelto: ";
            $state = '1';
            Shopify::updateOrInsert(
                ['shop' => $shop1, 'fApiUsr' => $fApiUsr, 'fApiClave' => $fApiClave],
                ['hmac' => $hmac, 'code' => $code, 'host' => $host, 'access_token' => $access_token, 'state' => $state]
            );

            //********************************************* */
            // URL de tu webhook
            $webhook_url = 'http://localhost/sf/psd_004.php';

            // Configura la URL de la API de Shopify
            $api_url = "https://$shop1/admin/api/2024-01";

            // Define los datos del webhook
            $webhook_data = [
                'webhook' => [
                    'topic' => 'orders/create',
                    'address' => $webhook_url,
                    'format' => 'json'
                ]
            ];
            // Configura las opciones de la solicitud
            $options = [
                'http' => [
                    'header' => "Content-type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode($webhook_data),
                ],
            ];

            // Realiza la solicitud para crear el webhook
            $context = stream_context_create($options);
            $curl_url = $api_url . '/webhooks.json';

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $curl_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{"webhook":{"address":"pubsub://projectName11:topicName","topic":"orders/create","format":"json"}}',
                CURLOPT_HTTPHEADER => array(
                    'X-Shopify-Topic: orders/create',
                    'X-Shopify-Shop-Domain: ' . $shop1,
                    'X-Shopify-API-Version: 2024-04',
                    'X-Shopify-Access-Token: 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',
                    'Content-Type: application/json',
                    'Cookie: request_method=POST'
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            echo 'otra prueba: ' . $response;

            // Verifica el resultado
            if ($result === FALSE) {
                $mensajeError =  "Error al crear el webhook";
                echo "<p style='color: red;'>$mensajeError</p>";
            } else {
                echo "<p style='color: green;'>Webhook creado exitosamente<br/><br/>";
            }
            Shopify::updateOrInsert(
                ['shop' => $shop1, 'fApiUsr' => $fApiUsr, 'fApiClave' => $fApiClave],
                ['token' => $response]
            );
            // Procesar los resultados
            $result = Shopify::all();//->each(function($shopifyDatos)
            #dd($result);
            $result->each(function($shopify){
                echo "Tienda: " . $shopify->shop . '<br/>';
                echo "TOKEN: " . $shopify->access_token . '<br/>';
                echo "Code: " . $shopify->code . '<br/>';
            });
        }
        return;
    }




    public function edit($id)
    {
        $shopifyDatos = Shopify::findOrFail($id);

        return view('shopify.edit', ['shopifyDatos' => $shopifyDatos]);
    }

    public function update($id, Request $request)
    {
        $shopifyDatos = Shopify::findOrFail($id);

        $request->validate([
            'shop' => 'required',
            'fApiUsr' => 'required',
            'fApiClave' => 'required',
        ]);

        $shopifyDatos->update([
            'shop' => $request->input('shop'),
            'fApiUsr' => $request->input('fApiUsr'),
            'fApiClave' => $request->input('fApiClave'),
        ]);

        return to_route('shopify.index');
    }

    public function destroy($id)
    {
        $shopifyDatos = Shopify::findOrFail($id);

        $shopifyDatos->delete();

        return to_route('shopify.index');
    }
}
