<?php
use Shopify\Clients\Rest;

$client = new Client();
$headers = [
  'X-Shopify-Access-Token' => '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',
  'Content-Type' => 'application/json',
  'Cookie' => '_master_udr=eyJfcmFpbHMiOnsibWVzc2FnZSI6IkJBaEpJaWswTlRSbE16VTJOaTFpWldOa0xUUTNaV1l0WW1Oa1pTMWhNMkU1TWpOaE1HUmhZVGtHT2daRlJnPT0iLCJleHAiOiIyMDI2LTA0LTIzVDE0OjU5OjI2LjA5MFoiLCJwdXIiOiJjb29raWUuX21hc3Rlcl91ZHIifX0%3D--b5faab10c2c268d3a3e24ad85db0e8bd10c0531b; _secure_admin_session_id=acf41654a2d1b113574730efd46990bc; _secure_admin_session_id_csrf=acf41654a2d1b113574730efd46990bc'
];
$body = '{
  "webhook": {
    "address": "zeusintegra.myshopify.com",
    "topic": "customers/update",
    "format": "json"
  }
}';
$request = new Request('POST', 'https://zeusintegra.myshopify.com/admin/api/2024-04/webhooks.json', $headers, $body);
$res = $client->sendAsync($request)->wait();
echo $res->getBody();
