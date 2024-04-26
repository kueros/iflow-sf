<?php

namespace App\Services;

use Shopify;
use Shopify\AuthHelper;
use App\Http\Controllers\ShopifyController;

class ShopifyService
{
    protected $shopify;

    public function __construct()
    {
        /*$this->shopify = Shopify::setShopUrl(config('shopify-api.api_url'))
            ->setApiKey(config('shopify-api.key'))
            ->setApiSecret(config('shopify-api.secret'))
            ->setAccessToken(AuthHelper::getAccessToken());
            */
    }

    public function getProducts()
    {
        return $this->shopify->get('/admin/api/' . config('shopify-api.api_version') . '/products.json');
    }

    // Add other methods as needed
}
