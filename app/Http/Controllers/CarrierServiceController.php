<?php

namespace App\Http\Controllers;

use App\Models\Shopify;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redirect;
use Shopify\Rest\Admin2024_04\CarrierService;
use Shopify\Utils;


class CarrierServiceController extends Controller
{
	/*************************************************************************************************************
     * ACA COMIENZA LA LOGICA DE LOS CARRIERS
	/*************************************************************************************************************
	/*************************************************************************************************************
	 * CARRIER CREATE
	 *
	 * @return \Illuminate\Http\Response
     * 
     * EVALUAR con Pablo qué datos se le van a pasar a la hora de la creación del carrier, supongo que será un json ccon lo necesario.
	 *************************************************************************************************************/
    public function carrierCreate()
    {
        $shopifyDatos = Shopify::latest()->first();
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

        $data = [ 
            "carrier_service" => [
                "name" => "IFLOW S.A.",
                "carrier_service_type" => "api",
                "callback_url" => "https://rate.requestcatcher.com",
                "format" => "json",
                "service_discovery" => true
            ]
        ];
# callback_url levantar desde el .env

$response = $api->callAPI('POST', 'carrier_services', $data);
        echo "<pre>";
        print_r($response);
        echo "</pre>";
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
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => 'pubsub://projectName:topicName',
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

        Shopify::updateOrInsert(
			['shop' => $shopifyDatos->shop, 'fApiUsr' => $shopifyDatos->fapiusr, 'fApiClave' => $shopifyDatos->fapiclave, 'access_token' => $shopifyDatos->access_token, 'updated_at' => now()],
			['created_at' => now(), 'webhook' => $responseJSON]
		);
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
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => 'pubsub://projectName:topicName',
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

        Shopify::updateOrInsert(
			['shop' => $shopifyDatos->shop, 'fApiUsr' => $shopifyDatos->fapiusr, 'fApiClave' => $shopifyDatos->fapiclave, 'access_token' => $shopifyDatos->access_token, 'updated_at' => now()],
			['created_at' => now(), 'webhook' => $responseJSON]
		);
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
        $api = new ShopifyAPI($shopifyDatos->shop, $shopifyDatos->access_token);

		// Datos iniciales en forma de arreglo asociativo
		$data = [
			"webhook" => [
				"address" => 'pubsub://projectName:topicName',
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

        Shopify::updateOrInsert(
			['shop' => $shopifyDatos->shop, 'fApiUsr' => $shopifyDatos->fapiusr, 'fApiClave' => $shopifyDatos->fapiclave, 'access_token' => $shopifyDatos->access_token, 'updated_at' => now()],
			['created_at' => now(), 'webhook' => $responseJSON]
		);
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

        $response = $api->callAPI('DELETE', "webhooks/{$webhookId}");

        // Procesa los datos del response encodificando el JSON
		#$responseJSON = json_encode($response, true);
        if (array_key_exists('errors', $response)) {
            echo "Error: " . $response['errors'];
        } else {
            echo "Webhook $webhookId borrado con éxito";
        }
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

