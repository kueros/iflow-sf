<?php

return [
    'api_key' => env('CLI_ID'),
    'shared_secret' => env("CLI_PASS"),
    'redirect_url' => env("RE_DIR_URL"),
    'logs' => env("FI_LOGS"),
    'scope' => env("SCOPE"),
    'webhook_url' => env("WEBHOOK_URL"),
    'url_root' => env("URL_ROOT"),
];
