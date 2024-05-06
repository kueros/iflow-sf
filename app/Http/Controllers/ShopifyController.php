<?php

namespace App\Http\Controllers;

use App\Models\Shopify;
use Illuminate\Http\Request;
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
			'fApiUsr' => 'required',
			'fApiClave' => 'required',
		]);

		Shopify::updateOrInsert(
			['fApiUsr' => $request->input('fApiUsr'), 'shop' => $request->input('shop')],
			['fApiClave' => $request->input('fApiClave'), 'created_at' => now(), 'updated_at' => now()]
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
				CURLOPT_POSTFIELDS => '{"webhook":{"address":"pubsub://projectName42:topicName","topic":"orders/create","format":"json"}}',
				CURLOPT_HTTPHEADER => array(
					'X-Shopify-Topic: orders/create',
					'X-Shopify-Shop-Domain: ' . $shopifyDatos->shop,
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
				['shop' => $shopifyDatos->shop, 'fApiUsr' => $shopifyDatos->fapiusr],
				['fApiClave' => $shopifyDatos->fapiclave, 'hmac' => $hmac, 'code' => $code, 'host' => $host, 'access_token' => $access_token, 'state' => $state, 'webhook' => $response,
				'created_at' => now(), 'updated_at' => now()]
			);
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
	 * CARRIERCREATE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function carrierCreate()
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$access_token = $shopifyDatos->access_token;
		$method = 'POST';
		$fApiClave = $shopifyDatos->fapiclave;
		$fApiUsr = $shopifyDatos->fapiusr;
		#dd($shopifyDatos);
		$curl = curl_init();

		// Datos iniciales en forma de arreglo asociativo

		$data = '{"carrier_service":
					{"id":1036894980,
						"name":"IFLOW S.A.",
						"carrier_service_type":"api",
						"admin_graphql_api_id":"gid://shopify/DeliveryCarrierService/1036894980",
						"callback_url":"https://rate.requestcatcher.com",
						"format":"json",
						"service_discovery":true
					}
				}';

#dd($data);
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://zeusintegra.myshopify.com/admin/api/2024-04/carrier_services.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			#CURLOPT_POSTFIELDS => '{"carrier_service":{"name":"Shipping Rate Provider","callback_url":"https://rate.requestcatcher.com","service_discovery":true}}',
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array(
				'X-Shopify-Access-Token: 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',
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		// Procesa los datos del response decodificando el JSON
		$responseJSON = json_decode($response, true);
		#echo "responseJSON";
		#var_dump($responseJSON);
		$carrierId = $responseJSON['carrier_service']['id'];
		// Muestra el JSON del response
		echo "JSON del response:";
		echo "<pre>";
		print_r($responseJSON);
		echo "</pre>";
		Shopify::updateOrInsert(
			['shop' => $shop, 'fApiUsr' => $fApiUsr, 'fApiClave' => $fApiClave, 'access_token' => $access_token, 'created_at' => now(), 'updated_at' => now()],
			['carrier' => $response]
		);
		if (str_contains($response, 'error')) {
			echo "La operación dio el siguiente error: " . $response;
			$mensaje = "La operación dio el siguiente error: " . $response;
		} else {
			echo "Carrier {$carrierId} creado con éxito";
			$mensaje = "Carrier {$carrierId} creado con éxito";
		}
		return $mensaje;
	}

	/*************************************************************************************************************
	 * CARREIERSHOW
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function carrierShow($carrierId)
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$access_token = $shopifyDatos->access_token;
		$method = 'GET';
		#dd($shopifyDatos);
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
						CURLOPT_URL => 'https://'.$shop.'/admin/api/2024-04/carrier_services/'.$carrierId.'.json',
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => '',
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 0,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => $method,
						CURLOPT_HTTPHEADER => array(
										'X-Shopify-Access-Token: '.$access_token
						),
		));
		
		$response = curl_exec($curl);
		
		curl_close($curl);
		// Procesa los datos del response decodificando el JSON
		$responseJSON = json_decode($response, true);
		// Muestra el JSON del response
		echo "JSON del response:";
		echo "<pre>";
		print_r($responseJSON);
		echo "</pre>";
	}

	/*************************************************************************************************************
	 * CARRIERLIST
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function carrierList()
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$access_token = $shopifyDatos->access_token;
		$method = 'GET';
		#dd($shopifyDatos);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://'.$shop.'/admin/api/2024-04/carrier_services.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => array(
							'X-Shopify-Access-Token: '.$access_token
			),
));

		$response = curl_exec($curl);

		curl_close($curl);
        curl_close($curl);
        // Procesa los datos del response decodificando el JSON
        $responseJSON = json_decode($response, true);

        // Muestra el JSON del response
        echo "JSON del response:";
        echo "<pre>";
        print_r($responseJSON);
        echo "</pre>";
    }



	/*************************************************************************************************************
	 * CARRIERDELETE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function carrierDelete($carrierId)
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$access_token = $shopifyDatos->access_token;
		$method = 'DELETE';

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://' . $shop . '/admin/api/2024-04/carrier_services/' . $carrierId . '.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => array(
				'X-Shopify-Access-Token: ' . $access_token
			),
		));

		$response = curl_exec($curl);
		// Valida si se ha producido errores y muestra el mensaje de error
		#dd(curl_errno($curl));
		if (str_contains($response, 'error')) {
			echo "La operación dio el siguiente error: " . $response;
		} else {
			echo "Carrier {$carrierId} borrado con éxito";
		}
	}



	/*************************************************************************************************************
	 * WEBHOOKCREATE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function webhookCreate()
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$fApiClave = $shopifyDatos->fapiclave;
		$fApiUsr = $shopifyDatos->fapiusr;
		$access_token = $shopifyDatos->access_token;
		$method = 'POST';

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => 'pubsub://projectName:topicName',
				"topic" => "customers/update",
				"format" => "json",
			]
		];
		$access_token = $shopifyDatos->access_token;

		// Convertir el arreglo a JSON
		$jsonData = json_encode($data);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://' . $shop . '/admin/api/2024-04/webhooks.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_HTTPHEADER => array(
				'X-Shopify-Access-Token: ' . $access_token,
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		#∫dd($response);
		// Procesa los datos del response decodificando el JSON
		$responseJSON = json_decode($response, true);
		$webhookId = $responseJSON['webhook']['id'];
		// Muestra el JSON del response
		echo "JSON del response:";
		echo "<pre>";
		print_r($responseJSON);
		echo "</pre>";
		Shopify::updateOrInsert(
			['shop' => $shop, 'fApiUsr' => $fApiUsr, 'fApiClave' => $fApiClave, 'access_token' => $access_token, 'updated_at' => now()],
			['created_at' => now(), 'webhook' => $response]
		);
		if (str_contains($response, 'error')) {
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
		$fApiClave = $shopifyDatos->fapiclave;
		$fApiUsr = $shopifyDatos->fapiusr;
		$access_token = $shopifyDatos->access_token;
		$method = 'POST';

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => 'pubsub://projectName:topicName',
				"topic" => "orders/paid",
				"format" => "json",
			]
		];
		$access_token = $shopifyDatos->access_token;

		// Convertir el arreglo a JSON
		$jsonData = json_encode($data);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://' . $shop . '/admin/api/2024-04/webhooks.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_HTTPHEADER => array(
				'X-Shopify-Access-Token: ' . $access_token,
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		#dd($response);
		// Procesa los datos del response decodificando el JSON
		$responseJSON = json_decode($response, true);
		$webhookId = $responseJSON['webhook']['id'];
		// Muestra el JSON del response
		echo "JSON del response:";
		echo "<pre>";
		print_r($responseJSON);
		echo "</pre>";
		Shopify::updateOrInsert(
			['shop' => $shop, 'fApiUsr' => $fApiUsr, 'fApiClave' => $fApiClave, 'access_token' => $access_token, 'updated_at' => now()],
			['created_at' => now(), 'webhook' => $response]
		);
		if (str_contains($response, 'error')) {
			echo "La operación dio el siguiente error: " . $response;
			$mensaje = "La operación dio el siguiente error: " . $response;
		} else {
			echo "Webhook {$webhookId} creado con éxito";
			$mensaje = "Webhook {$webhookId} creado con éxito";
		}
		return $mensaje;
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
		$fApiClave = $shopifyDatos->fapiclave;
		$fApiUsr = $shopifyDatos->fapiusr;
		$access_token = $shopifyDatos->access_token;
		$method = 'POST';

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => 'pubsub://projectName:topicName',
				"topic" => "orders/cancelled",
				"format" => "json",
			]
		];
		$access_token = $shopifyDatos->access_token;

		// Convertir el arreglo a JSON
		$jsonData = json_encode($data);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://' . $shop . '/admin/api/2024-04/webhooks.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $jsonData,
			CURLOPT_HTTPHEADER => array(
				'X-Shopify-Access-Token: ' . $access_token,
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		#∫dd($response);
		// Procesa los datos del response decodificando el JSON
		$responseJSON = json_decode($response, true);
		$webhookId = $responseJSON['webhook']['id'];
		// Muestra el JSON del response
		echo "JSON del response:";
		echo "<pre>";
		print_r($responseJSON);
		echo "</pre>";
		Shopify::updateOrInsert(
			['shop' => $shop, 'fApiUsr' => $fApiUsr, 'fApiClave' => $fApiClave, 'access_token' => $access_token, 'updated_at' => now()],
			['created_at' => now(), 'webhook' => $response]
		);
		if (str_contains($response, 'error')) {
			echo "La operación dio el siguiente error: " . $response;
			$mensaje = "La operación dio el siguiente error: " . $response;
		} else {
			echo "Webhook {$webhookId} creado con éxito";
			$mensaje = "Webhook {$webhookId} creado con éxito";
		}
		return $mensaje;
	}

	/*************************************************************************************************************
	 * WEBHOOKSHOW
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function webhookShow($webhookId)
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$access_token = $shopifyDatos->access_token;
		$method = 'GET';
dd($shop);
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://' . $shop . '/admin/api/2024-04/webhooks/' . $webhookId . '.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => array(
				'X-Shopify-Access-Token: ' . $access_token
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		// Procesa los datos del response decodificando el JSON
		$responseJSON = json_decode($response, true);
		// Muestra el JSON del response
		echo "JSON del response:";
		echo "<pre>";
		print_r($responseJSON);
		echo "</pre>";
	}

	/*************************************************************************************************************
	 * WEBHOOKDELETE
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function webhookDelete($webhookId)
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$access_token = $shopifyDatos->access_token;
		$method = 'DELETE';

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://' . $shop . '/admin/api/2024-04/webhooks/' . $webhookId . '.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => array(
				'X-Shopify-Access-Token: ' . $access_token
			),
		));

		$response = curl_exec($curl);
		// Valida si se ha producido errores y muestra el mensaje de error
		#dd(curl_errno($curl));
		if (str_contains($response, 'error')) {
			echo "La operación dio el siguiente error: " . $response;
		} else {
			echo "Webhook {$webhookId} borrado con éxito";
		}
	}
	/*************************************************************************************************************
	 * webhooklist
	 *
	 * @return \Illuminate\Http\Response
	 *************************************************************************************************************/
	public function webhookList()
	{
		$shopifyDatos = Shopify::latest()->first();
		$shop = $shopifyDatos->shop;
		$access_token = $shopifyDatos->access_token;
		$method = 'GET';

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://'.$shop.'/admin/api/2024-07/webhooks.json',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => array(
				'X-Shopify-Access-Token: ' . $access_token
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		// Procesa los datos del response decodificando el JSON
		$responseJSON = json_decode($response, true);

		// Muestra el JSON del response
		echo "JSON del response:";
		echo "<pre>";
		print_r($responseJSON);
		echo "</pre>";
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
