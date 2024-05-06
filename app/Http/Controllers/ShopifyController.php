<?php

namespace App\Http\Controllers;

use App\Models\Shopify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
use Shopify\Rest\Admin2024_04\CarrierService;
use Shopify\Utils;

class ShopifyController extends Controller
{
	protected $url_root;

	public function __construct()
	{
		$this->url_root = config('sfenv.url_root');
	}

	/*************************************************************************************************************
	 * INDEX
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function index()
	{
		$shopifyDatos = Shopify::query()
			->orderByDesc('id')
			->get();

		return view('shopify.index')->with('shopifyDatos', $shopifyDatos);
	}

	/*************************************************************************************************************
	 * STORE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function store(Request $request)
	{
		#Valido los datos enviados desde el formulario
		$request->validate([
			'shop' => 'required',
			'fapiusr' => 'required',
			'fapiclave' => 'required',
		]);

		Shopify::updateOrInsert(
			['fapiusr' => $request->input('fapiusr'), 'shop' => $request->input('shop')],
			['fapiclave' => $request->input('fapiclave'), 'created_at' => now(), 'updated_at' => now()]
		);
		return redirect($this->url_root . '/install');
	}
	/*************************************************************************************************************
	 * INSTALL
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
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
		#dd($shopifyDatos);
		$install = "https://" . $shopifyDatos->shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scope . "&redirect_uri=" . $redirect_url;
		#dd($install);
		return redirect($install);
	}
	/*************************************************************************************************************
	 * SEGUNDOWEBHOOK
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function segundowebhook()
	{
		#Me traigo la última tienda creada desde la tabla
		$shopifyDatos = Shopify::latest()->first();
		#Cargo los datos desde el .env
		$api_key = env('CLI_ID');
		$shared_secret = env('CLI_PASS');
		#Cargo los datos desde el formulario
		$params = $_GET;
		$hmac = isset($_GET['hmac']) ? $_GET['hmac'] : '';
		$code = isset($_GET['code']) ? $_GET['code'] : '';
		$state = isset($_GET['state']) ? $_GET['state'] : '';
		$host = isset($_GET['host']) ? $_GET['host'] : '';

		$params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
		ksort($params); // Sort params lexigraphically
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
			// Configura la URL de la API de Shopify
			$api_url = "https://$shopifyDatos->shop/admin/api/2024-01";

			// Realiza la solicitud para crear el webhook
			$curl_url = $api_url . '/webhooks.json';

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_URL => $curl_url,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => '{"webhook":{"address":"pubsub://projectName45:topicName","topic":"orders/create","format":"json"}}',
				CURLOPT_HTTPHEADER => array(
					'X-Shopify-Topic: orders/create',
					'X-Shopify-Shop-Domain: ' . $shopifyDatos->shop,
					'X-Shopify-API-Version: 2024-04',
					'X-Shopify-Access-Token: ' . $access_token,
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
			$shopify = Shopify::create([
				'shop' => $shopifyDatos->shop, 
				'fapiusr' => $shopifyDatos->fapiusr,
				'fapiclave' => $shopifyDatos->fapiclave, 
				'hmac' => $hmac, 
				'code' => $code, 
				'host' => $host, 
				'access_token' => $access_token, 
				'state' => $state, 
				'webhook' => $response,
				'created_at' => now(), 
				'updated_at' => now()
			]);
			$shopify->save();

			

			// Procesar los resultados
			$result = Shopify::all(); //->each(function($shopifyDatos)
			#dd($result);
			$result->each(function ($shopify) {
				echo "Tienda: " . $shopify->shop . '<br/>';
				echo "TOKEN: " . $shopify->access_token . '<br/>';
				echo "Code: " . $shopify->code . '<br/>';
			});
		}
		$crearShipingCarrier = $this->carrierCreate();
		$crearWebhookOrdersPaid = $this->webhookCreateOrdersPaid();
		$crearWebhookOrdersCancelled = $this->webhookCreateOrdersCancelled();
		echo "<pre>";
		print_r($crearShipingCarrier);
		print_r($crearWebhookOrdersPaid);
		print_r($crearWebhookOrdersCancelled);
		echo "</pre>";

		return;
	}

	/*************************************************************************************************************
	 * ACA COMIENZA LA LOGICA DE LOS CARRIERS
	/*************************************************************************************************************
	/*************************************************************************************************************
	 * CARRIER CREATE
	 *
	 * @return \Illuminate\Http\Response
	 * 
	 *************************************************************************************************************/
	public function carrierCreate()
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$fapiusr = $shopifyDatos->fapiusr;
		$fapiclave = $shopifyDatos->fapiclave;
		$access_token = $shopifyDatos->access_token;
		$api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);
		$callback_url = env('CALLBACK_URL_CARRIER',);

		$data = [
			"carrier_service" => [
				"name" => "IFLOW S.A.",
				"carrier_service_type" => "api",
				"callback_url" => $callback_url,
				"format" => "json",
				"service_discovery" => true
			]
		];
		# callback_url levantar desde el .env
        $response = $api->callAPI('POST', 'carrier_services', $data);
        echo "<pre>";
        print_r($response);
        echo "</pre>";
		$carrierId = $response['carrier_service']['id'];
        // Procesa los datos del response encodificando el JSON
		$responseJSON = json_encode($response, true);
#dd($fapiusr.' - '.$fapiclave);
		$shopify = Shopify::create([
			'shop' => $shop, 
			'fapiusr' => $fapiusr,
			'fapiclave' => $fapiclave, 
			'access_token' => $access_token,
			'carrier' => $responseJSON,
			'created_at' => now(), 
			'updated_at' => now()
		]);
		$shopify->save();

		if (str_contains($responseJSON, 'error')) {
			echo "La operación dio el siguiente error: " . $response;
		} else {
			echo "Webhook {$carrierId} creado con éxito";
		}

	}





    /*************************************************************************************************************
	 * CARRIER SHOW
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
    public function carrierShow($carrierId)
    {
        $shopifyDatos = Shopify::latest()->first();
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

        $response = $api->callAPI('GET', "carrier_services/{$carrierId}");
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }

	/*************************************************************************************************************
	 * CARRIER LIST
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
    public function carrierList()
    {
        $shopifyDatos = Shopify::latest()->first();
        #dd($shopifyDatos);
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

        $response = $api->callAPI('GET', "carrier_services");
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }

	/*************************************************************************************************************
	 * CARRIER DELETE
	 *
	 * @return \Illuminate\Http\Response
     * 
     * 
     * En los delete agregar la sentencia de borrado de datos en la tabla.
     * 
     * 
     * 
	 *************************************************************************************************************/
    public function carrierDelete($carrierId)
    {
		$shopifyDatos = DB::table('Shopify')
		->where('carrier->carrier_service->id', $carrierId)
		->get();
		$shopifyDatos = Shopify::latest()->first();
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);
        #$api = new ShopifyAPI("zeusintegra.mishopify.com", "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");

        $response = $api->callAPI('DELETE', "carrier_services/{$carrierId}");
		$shopifyDatos = DB::table('Shopify')
		->where('carrier->carrier_service->id', $carrierId)
		->delete();

		$shopifyDatos = Shopify::latest()->first();
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

        $response = $api->callAPI('DELETE', "carrier_services/{$carrierId}");
        if (array_key_exists('error', $response)) {
            echo "Error: " . $response['error'];
        } else {
            echo "Carrier $carrierId borrado con éxito";
        }
    }



	/*************************************************************************************************************
     * ACA COMIENZA LA LOGICA DE LOS WEBHOOKS
	/*************************************************************************************************************
	/*************************************************************************************************************
	 * WEBHOOKCREATE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function webhookCreate()
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$fapiusr = $shopifyDatos->fapiusr;
		$fapiclave = $shopifyDatos->fapiclave;
		$access_token = $shopifyDatos->access_token;
		$webhook_address_orders_create = env('WEBHOOK_ADDRESS_ORDERS_CREATE');
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => $webhook_address_orders_create,
				"topic" => "customers/update",
				"format" => "json",
			]
		];
        $response = $api->callAPI('POST', 'webhooks', $data);
        echo "<pre>";
        print_r($response);
        echo "</pre>";
		$webhookId = $response['webhook']['id'];
        // Procesa los datos del response encodificando el JSON
		$responseJSON = json_encode($response, true);

		$shopify = Shopify::create([
			'shop' => $shop, 
			'fapiusr' => $fapiusr,
			'fapiclave' => $fapiclave, 
			'access_token' => $access_token,
			'webhook' => $responseJSON,
			'created_at' => now(), 
			'updated_at' => now()
		]);
		$shopify->save();

		if (str_contains($responseJSON, 'error')) {
			echo "La operación dio el siguiente error: " . $response;
		} else {
			echo "Webhook {$webhookId} creado con éxito";
		}
	}

	/*************************************************************************************************************
	 * WEBHOOKCREATE ORDERS/PAID
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function webhookCreateOrdersPaid()
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$fapiusr = $shopifyDatos->fapiusr;
		$fapiclave = $shopifyDatos->fapiclave;
		$access_token = $shopifyDatos->access_token;
		$webhook_address_orders_paid = env('WEBHOOK_ADDRESS_ORDERS_PAID');
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => $webhook_address_orders_paid,
				"topic" => "orders/paid",
				"format" => "json",
			]
		];
        $response = $api->callAPI('POST', 'webhooks', $data);
        echo "<pre>";
        print_r($response);
        echo "</pre>";
		$webhookId = $response['webhook']['id'];
        // Procesa los datos del response encodificando el JSON
		$responseJSON = json_encode($response, true);

		$shopify = Shopify::create([
			'shop' => $shop, 
			'fapiusr' => $fapiusr,
			'fapiclave' => $fapiclave, 
			'access_token' => $access_token,
			'webhook' => $responseJSON,
			'created_at' => now(), 
			'updated_at' => now()
		]);
		$shopify->save();

		if (str_contains($responseJSON, 'error')) {
			echo "La operación dio el siguiente error: " . $response;
		} else {
			echo "Webhook {$webhookId} creado con éxito";
		}
	}

	/*************************************************************************************************************
	 * WEBHOOKCREATE ORDERS/CANCELLED
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function webhookCreateOrdersCancelled()
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$fapiusr = $shopifyDatos->fapiusr;
		$fapiclave = $shopifyDatos->fapiclave;
		$access_token = $shopifyDatos->access_token;
		$webhook_address_orders_cancelled = env('WEBHOOK_ADDRESS_ORDERS_CANCELLED');
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => $webhook_address_orders_cancelled,
				"topic" => "orders/cancelled",
				"format" => "json",
			]
		];
        $response = $api->callAPI('POST', 'webhooks', $data);
        echo "<pre>";
        print_r($response);
        echo "</pre>";
		$webhookId = $response['webhook']['id'];
        // Procesa los datos del response encodificando el JSON
		$responseJSON = json_encode($response, true);

		$shopify = Shopify::create([
			'shop' => $shop, 
			'fapiusr' => $fapiusr,
			'fapiclave' => $fapiclave, 
			'access_token' => $access_token,
			'webhook' => $responseJSON,
			'created_at' => now(), 
			'updated_at' => now()
		]);
		$shopify->save();

		if (str_contains($responseJSON, 'error')) {
			echo "La operación dio el siguiente error: " . $response;
		} else {
			echo "Webhook {$webhookId} creado con éxito";
		}
	}

     /*************************************************************************************************************
	 * WEBHOOK SHOW
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
    public function webhookShow($webhookId)
    {
        $shopifyDatos = Shopify::latest()->first();
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

        $response = $api->callAPI('GET', "webhooks/{$webhookId}");
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }

	/*************************************************************************************************************
	 * WEBHOOK LIST
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
    public function webhookList()
    {
        $shopifyDatos = Shopify::latest()->first();
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

        $response = $api->callAPI('GET', "webhooks");
        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }

	/*************************************************************************************************************
	 * WEBHOOK DELETE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
    public function webhookDelete($webhookId)
    {
        $shopifyDatos = Shopify::latest()->first();
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

		$shopifyDatos = DB::table('Shopify')
		->where('webhook->webhook->id', $webhookId)
		->get();

        $response = $api->callAPI('DELETE', "webhooks/{$webhookId}");
		$shopifyDatos = DB::table('Shopify')
		->where('webhook->webhook->id', $webhookId)
		->delete();
        // Procesa los datos del response encodificando el JSON
		#$responseJSON = json_encode($response, true);
        if (array_key_exists('errors', $response)) {
            echo "Error: " . $response['errors'];
        } else {
            echo "Webhook $webhookId borrado con éxito";
        }
    }



	/*************************************************************************************************************
	 * EDIT
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function edit($id)
	{
		$shopifyDatos = Shopify::findOrFail($id);

		return view('shopify.edit', ['shopifyDatos' => $shopifyDatos]);
	}

	/*************************************************************************************************************
	 * UPDATE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function update($id, Request $request)
	{
		$shopifyDatos = Shopify::findOrFail($id);

		$request->validate([
			'shop' => 'required',
			'fapiusr' => 'required',
			'fapiclave' => 'required',
		]);

		$shopifyDatos->update([
			'shop' => $request->input('shop'),
			'fapiusr' => $request->input('fapiusr'),
			'fapiclave' => $request->input('fapiclave'),
		]);

		return to_route('shopify.index');
	}

	/*************************************************************************************************************
	 * DESTROY
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function destroy($id)
	{
		$shopifyDatos = Shopify::findOrFail($id);

		$shopifyDatos->delete();

		return to_route('shopify.index');
	}
	/*************************************************************************************************************
	 * SHOW
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/

	public function show(int $id)
	{
		return 'Detalle de la Tienda: ' . $id;
	}

	/*************************************************************************************************************
	 * CREATE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function create()
	{
		return view('shopify.create');
	}
}


/*************************************************************************************************************
 * SHOPIFY API
 *
 * @return \Illuminate\Http\Response
 *************************************************************************************************************/

class ShopifyAPI
{
	private $shop;
	private $accessToken;

	public function __construct($shop, $accessToken)
	{
		$this->shop = $shop;
		$this->accessToken = $accessToken;
	}

	/*************************************************************************************************************
	 * CALL API
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function callAPI($method, $endpoint, $data = null)
	{
		$curl = curl_init();
		$url = "https://{$this->shop}/admin/api/2024-04/{$endpoint}.json";

		$options = [
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => [
				'X-Shopify-Access-Token: ' . $this->accessToken,
				'Content-Type: application/json'
			],
		];

		if ($data !== null) {
			$options[CURLOPT_POSTFIELDS] = json_encode($data);
		}

		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		curl_close($curl);

		return json_decode($response, true);
	}
}
