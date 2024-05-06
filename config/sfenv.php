<?php

return [
    'api_key' => env('CLI_ID'),
    'shared_secret' => env("CLI_PASS"),
    'redirect_url' => env("RE_DIR_URL"),
    'logs' => env("FI_LOGS"),
    'scope' => env("SCOPE"),
    'webhook_url' => env("WEBHOOK_URL"),
    'urlroot' => env("URLROOT"),
    'callback_url_carrier' => env("CALLBACK_URL_CARRIER"),
    'webhook_address_orders_create' => env("WEBHOOK_ADDRESS_ORDERS_CREATE"),
    'webhook_address_orders_paid' => env("WEBHOOK_ADDRESS_ORDERS_PAID"),
    'webhook_address_orders_cancelled' => env("WEBHOOK_ADDRESS_ORDERS_CANCELLED"),
    ];
