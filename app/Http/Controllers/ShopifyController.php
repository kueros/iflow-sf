<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ShopifyService;
use Shopify\Rest\Admin2024_04\Webhook;
use Shopify\Utils;
use Shopify;

class ShopifyController extends Controller
{
    protected $shopifyService;
    protected $test_session;


    public function __construct(ShopifyService $shopifyService)
    {
        $this->shopifyService = $shopifyService;
    }

    public function index(Request $request)
    {
        // Obtener los encabezados de la solicitud HTTP
        $requestHeaders = $request->header();
        $isOnline = true;
        $apiKey = 'a63d7f3c5ab33dd09bd3424b58d83782';
        $apiSecret = 'd02d01e10a952e9dc090e67ed8e1f138';
        
        // Definir la clave de autorización en los encabezados
        $requestHeaders = [
            'X-Shopify-Api-Key' => $apiKey,
            'X-Shopify-Api-Secret' => $apiSecret,
            'Content-Type' => 'application/json',
        ];
            
        // Cargar la sesión de prueba (si es necesario)
        $this->test_session = Utils::loadCurrentSession(
            $requestHeaders,
            $request->cookies->all(),
            $isOnline  // Asegúrate de definir $isOnline adecuadamente
    
        #$products = $this->shopifyService->getProducts();
        );
        echo $requestHeaders;
    
        $webhook = new Webhook($this->test_session);
        $webhook->address = "pubsub://projectName:topicName";
        $webhook->topic = "customers/update";
        $webhook->format = "json";
        $webhook->save(
            true, // Update Object
        );
    
        // Handle the response

        return response()->json($products);
    }

    // Add other methods as needed
}


