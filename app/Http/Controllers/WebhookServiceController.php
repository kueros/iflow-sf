<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookServiceController extends Controller
{
    private $shop;
    private $accessToken;

    public function __construct($shop, $accessToken)
    {
        $this->shop = $shop;
        $this->accessToken = $accessToken;
    }

    public function createWebhook($topic, $address)
    {
        $method = 'POST';
        $data = [
            "webhook" => [
                "address" => $address,
                "topic" => $topic,
                "format" => "json",
            ]
        ];
        return $this->makeRequest('webhooks', $method, $data);
    }

    public function showWebhook($webhookId)
    {
        $method = 'GET';
        return $this->makeRequest("webhooks/{$webhookId}", $method);
    }

    public function deleteWebhook($webhookId)
    {
        $method = 'DELETE';
        return $this->makeRequest("webhooks/{$webhookId}", $method);
    }

    public function listWebhooks()
    {
        $method = 'GET';
        $this->shop = "zeusintegra.myshopify.com";
        return $this->makeRequest('webhooks', $method);
    }

    private function makeRequest($endpoint, $method, $data = null)
    {
        $url = "https://{$this->shop}/admin/api/2024-07/{$endpoint}.json";
        $curl = curl_init();

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

        if ($method === 'POST' && $data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

}




