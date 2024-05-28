<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\InstallLog;
use App\Models\Webhook;
use App\Models\Config;
use App\Models\CarrierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
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
		$storeDatos = Store::query()
			->orderByDesc('id')
			->get();
		return view('shopify.index')->with('shopifyDatos', $storeDatos);
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
			'cuit' => 'required',
		]);
		Store::updateOrInsert(
			['shop' => $request->input('shop')],
			['fapiusr' => $request->input('fapiusr'), 'fapiclave' => $request->input('fapiclave'),'cuit' => $request->input('cuit'), 'created_at' => now(), 'updated_at' => now()]
		);
		$storeShopId = Store::latest()->first();
		InstallLog::updateOrInsert(
			['shopId' => $storeShopId->id, 'shop' => $request->input('shop')],
			['fapiusr' => $request->input('fapiusr'), 'fapiclave' => $request->input('fapiclave'), 'created_at' => now(), 'updated_at' => now()]
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
		#Cargo las variables desde la tabla de configs
        $configs = Config::get()->first();
        #dd($configs);
		$api_key = $configs->cli_id;//config('sfenv.api_key');
		$redirect_url =  $configs->re_dir_url;
		$scope =  $configs->scope;

		#Chupo los storeDatos del último registro de la tabla
		$storeDatos = Store::latest()->first();
		$install = "https://" . $storeDatos->shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scope . "&redirect_uri=" . $redirect_url;
		#Desde aquí se llama al método segundowebhook que figura a continuación
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
		$storeDatos = Store::latest()->first();
        #Cargo las variables desde la tabla de configs
        $configs = Config::get()->first();
        $api_key = $configs->cli_id;
        $shared_secret = $configs->cli_pass;
        $api_key = $configs->cli_id;
        $webhook_address_orders_create = $configs->webhook_address_orders_create;
		
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
			$shop = $params['shop'];
			

			// Generate access token URL
			$access_token_url = "https://" . $shop . "/admin/oauth/access_token";

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
			$access_token = $result['access_token'];
			//dd($access_token);
			
			// Show the access token (don't do this in production!)
			$state = 'Activo';
			// Configura la URL de la API de Shopify

			$api_url = 'https://'.$storeDatos->shop .'/admin/api/2024-04/webhooks.json';

			// var_dump ($api_url);exit;

			// Realiza la solicitud para crear el webhook
			//$curl_url = $api_url . '/webhooks.json';
			$curl_url = $api_url ; 

				
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
				CURLOPT_POSTFIELDS => '{
					"webhook":
						{"address":"'.$webhook_address_orders_create .'",
							"topic":"orders/create",
							"format":"json"}
						}',
				CURLOPT_HTTPHEADER => array(
					'X-Shopify-Topic: orders/create',
					'X-Shopify-Shop-Domain: ' . $storeDatos->shop,
					'X-Shopify-API-Version: 2024-04',
					'X-Shopify-Access-Token: ' . $access_token,
					'Content-Type: application/json',
					'Cookie: request_method=POST'
				),
			));

			

			$response = curl_exec($curl);
			
			//var_dump($response);

			//var_dump('psd'. json_decode($response));
			//exit;
			
			curl_close($curl);

			// Verifica el resultado
			if ($result === FALSE) {
				$mensajeError =  "Error al crear el webhook";
				echo "<p style='color: red;'>$mensajeError</p>";
			} else {
				echo "<p style='color: green;'>Webhook creado exitosamente<br/><br/>";
			}
			# Actualizo el registro de la tabla Stores
			Store::updateOrInsert(
				[
					'shop' => $storeDatos->shop
				],
				[
					'token' => $access_token, 
					'code' => $code, 
					'cuit' => $storeDatos->cuit, 
					'fapiusr' => $storeDatos->fapiusr,
					'fapiclave' => $storeDatos->fapiclave, 
					'hmac' => $hmac, 
					'host' => $host, 
					'state' => $state, 
					'created_at' => now(), 
					'updated_at' => now()
				]);
	

			# Creo el registro y guardo los datos en la tabla Webhooks
			$shopId = Store::latest()->first();
			
			
			$responseArray = $response;

			$responseArray = json_decode($response, true);
			
			

			#dd($responseArray['webhook']['id']);

			$webhookId = $responseArray['webhook']['id'];
			$webhook = Webhook::create([
				'webhookId' => $webhookId, 
				'shopId' => $shopId->id, 
				'url' => $responseArray['webhook']['address'], 
				'tipo' => $responseArray['webhook']['topic'], 
				'state' => $state, 
				'created_at' => now(), 
				'updated_at' => now()
			]);
			$webhook->save();

			InstallLog::updateOrInsert(
				[
					'shop' => $storeDatos->shop
				],
				[
					'shopId' => $shopId->id, 
					'token' => $access_token, 
					'code' => $code, 
					'cuit' => $storeDatos->cuit, 
					'shop' => $storeDatos->shop, 
					'fapiusr' => $storeDatos->fapiusr,
					'fapiclave' => $storeDatos->fapiclave, 
					'hmac' => $hmac, 
					'host' => $host, 
					'state' => $state, 
					'created_at' => now(), 
					'updated_at' => now()
				]);

			// Procesar los resultados
			$result = Store::all(); //->each(function($shopifyDatos)
			#dd($result);
			$result->each(function ($store) {
				//$psd = $store->shop;
				echo "Tienda: " . $store->shop . '<br/>';
				echo "TOKEN: " . $store->access_token . '<br/>';
				echo "Code: " . $store->code . '<br/>';
			});
		}

		echo "access_token: " . $access_token . '<br/>'; 

		$crearShipingCarrier = $this->carrierCreate($access_token);
		$crearWebhookOrdersPaid = $this->webhookCreateOrdersPaid($access_token);
		$crearWebhookOrdersCancelled = $this->webhookCreateOrdersCancelled($access_token);
		echo "<pre>";
		print_r($crearShipingCarrier);
		print_r($crearWebhookOrdersPaid);
		print_r($crearWebhookOrdersCancelled);
		echo "</pre>";

		//return;
		return redirect()->to('https://track.iflow21.com/');
		
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
	public function carrierCreate($access_token)
	{
		$storeDatos = Store::latest()->first();
        $configs = Config::get()->first();
		$shop = $storeDatos->shop;
		$api = new ShopifyAPI($storeDatos->shop, $access_token);
		#$callback_url = env('CALLBACK_URL_CARRIER',);
        $callback_url = $configs->callback_url_carrier;
		
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

		$state = 'Activo';
		# Creo el registro y guardo los datos en la tabla Webhooks
		$shopId = Store::latest()->first();
		$shop = Store::latest()->first('shop');
		$shopNombre = $shop['shop'];

		$carrierId = $response['carrier_service']['id'];
		$carrierCallbackUrl = $response['carrier_service']['callback_url'];
		$carrierTipo = $response['carrier_service']['admin_graphql_api_id'];
		$carrier_services = CarrierService::create([
			'carrierServiceId' => $carrierId, 
			'shopId' => $shopId->id, 
			'callbackUrl' => $carrierCallbackUrl, 
			'tipo' => $carrierTipo, 
			'nombre' => $shopNombre, 
			'state' => $state, 
			'created_at' => now(), 
			'updated_at' => now()
		]);
		$carrier_services->save();
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
		$shopifyDatos = Store::latest()->first();
		$shop = $shopifyDatos->shop;
		$fapiusr = $shopifyDatos->fapiusr;
		$fapiclave = $shopifyDatos->fapiclave;
		$access_token = $shopifyDatos->access_token;
        #$webhook_address_orders_create = env('WEBHOOK_ADDRESS_ORDERS_CREATE');
        $configs = Config::get()->first();
        $webhook_address_orders_create = $configs->webhook_address_orders_create;
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

		$shopify = Store::create([
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
	public function webhookCreateOrdersPaid($access_token)
	{
		$storeDatos = Store::latest()->first();
        #$webhook_address_orders_paid = env('WEBHOOK_ADDRESS_ORDERS_PAID');
        $configs = Config::get()->first();
        $webhook_address_orders_paid = $configs->webhook_address_orders_paid;
        $api = new ShopifyAPI($storeDatos->shop, $access_token);

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => $webhook_address_orders_paid,
				"topic" => "orders/paid",
				"format" => "json",
			]
		];
        $response = $api->callAPI('POST', 'webhooks', $data);
        
		#dd($response);  psd subir 
		// Procesa los datos del response
		$state = 'Activo';
		$shopId = Store::latest()->first();
		$webhookId = $response['webhook']['id'];
		$webhookUrl = $response['webhook']['address'];
		$webhookTipo = $response['webhook']['topic'];
		$webhook = Webhook::create([
			'webhookId' => $webhookId, 
			'shopId' => $shopId->id, 
			'url' => $webhookUrl, 
			'tipo' => $webhookTipo, 
			'state' => $state, 
			'created_at' => now(), 
			'updated_at' => now()
		]);
		$webhook->save();
	}

	/*************************************************************************************************************
	 * WEBHOOKCREATE ORDERS/CANCELLED
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function webhookCreateOrdersCancelled($access_token)
	{
		$storeDatos = Store::latest()->first();
        #$webhook_address_orders_cancelled = env('WEBHOOK_ADDRESS_ORDERS_CANCELLED');
        $configs = Config::get()->first();
        $webhook_address_orders_cancelled = $configs->webhook_address_orders_cancelled;
        $api = new ShopifyAPI($storeDatos->shop, $access_token);

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => $webhook_address_orders_cancelled,
				"topic" => "orders/cancelled",
				"format" => "json",
			]
		];
        $response = $api->callAPI('POST', 'webhooks', $data);
        // Procesa los datos del response
		$state = 'Activo';
		$shopId = Store::latest()->first();
		$webhookId = $response['webhook']['id'];
		$webhookUrl = $response['webhook']['address'];
		$webhookTipo = $response['webhook']['topic'];
		$webhook = Webhook::create([
			'webhookId' => $webhookId, 
			'shopId' => $shopId->id, 
			'url' => $webhookUrl, 
			'tipo' => $webhookTipo, 
			'state' => $state, 
			'created_at' => now(), 
			'updated_at' => now()
		]);
		$webhook->save();
	}










    /*************************************************************************************************************
     * CARRIER SHOW
     *
     * @return \Illuminate\Http\Response
     *************************************************************************************************************/
    public function carrierShow($carrierId)
    {
        $storeDatos = Store::latest()->first();
        $api = new ShopifyAPI($storeDatos->shop, $storeDatos->access_token);

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
        $shopifyDatos = Store::latest()->first();

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
     *************************************************************************************************************/
    public function carrierDelete($carrierId)
    {
        $shopifyDatos = DB::table('Shopify')
        ->where('carrier->carrier_service->id', $carrierId)
            ->get();
        $shopifyDatos = Store::latest()->first();
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);
        #$api = new ShopifyAPI("zeusintegra.mishopify.com", "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");

        $response = $api->callAPI('DELETE', "carrier_services/{$carrierId}");
        $shopifyDatos = DB::table('Shopify')
        ->where('carrier->carrier_service->id', $carrierId)
            ->delete();

        $shopifyDatos = Store::latest()->first();
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

        $response = $api->callAPI('DELETE', "carrier_services/{$carrierId}");
        if (array_key_exists('error', $response)) {
            echo "Error: " . $response['error'];
        } else {
            echo "Carrier $carrierId borrado con éxito";
        }
    }

     /*************************************************************************************************************
	 * WEBHOOK SHOW
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
    public function webhookShow($webhookId)
    {
        $shopifyDatos = Store::latest()->first();
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
        $shopifyDatos = Store::latest()->first();
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
        $shopifyDatos = Store::latest()->first();
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
		$shopifyDatos = Store::findOrFail($id);

		return view('shopify.edit', ['shopifyDatos' => $shopifyDatos]);
	}

	/*************************************************************************************************************
	 * UPDATE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function update($id, Request $request)
	{
		$shopifyDatos = Store::findOrFail($id);

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
		$shopifyDatos = Store::findOrFail($id);

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
