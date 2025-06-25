<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
'allowed_origins' => [
    'http://localhost:5173',
    'http://localhost:5174',
    'https://itcshop-customer.netlify.app', 
    'https://itcshop-admin.netlify.app',
],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
