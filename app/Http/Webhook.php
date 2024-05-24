<?php
use Shopify\Rest\Admin2024_04\Webhook;
use Shopify\Utils;



    $this->test_session = Utils::loadCurrentSession(
        $requestHeaders,
        $requestCookies,
        $isOnline
    );
echo $requestHeaders;

    $webhook = new Webhook($this->test_session);
    $webhook->address = "pubsub://projectName:topicName";
    $webhook->topic = "customers/update";
    $webhook->format = "json";
    $webhook->save(
        true, // Update Object
    );
