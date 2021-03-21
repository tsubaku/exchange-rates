<?php

require_once './api/CurrencyApi.php';

try {
    $api = new currencyApi();
    echo $api->run();
} catch (Exception $e) {
    echo json_encode([
            'error' => $e->getMessage()
    ]);
}
