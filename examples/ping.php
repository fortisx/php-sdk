<?php

require __DIR__ . '/../vendor/autoload.php';

use FortisX\SDK\API;
use FortisX\SDK\APIError;

$api = new API('demo-key');

try {
    $res = $api->get('ping');

    print_r($res);
} catch (APIError $err) {
    fwrite(STDERR, "API error [{$err->status}]: {$err->getMessage()}" . PHP_EOL);

    if (!empty($err->details)) {
        fwrite(STDERR, json_encode($err->details, JSON_PRETTY_PRINT) . PHP_EOL);
    }
}