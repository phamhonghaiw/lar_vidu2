<?php

return [
    // Set to * only when the service is reachable through a trusted host proxy.
    'trusted_proxies' => env('TRUSTED_PROXIES') === '*'
        ? '*'
        : array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),
];
