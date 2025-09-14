<?php
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'wifi_billing', // ✅ your real database name
        'user' => 'root',         // ✅ your MySQL user
        'pass' => '',             // ✅ your MySQL password
        'charset' => 'utf8mb4'
    ],

    'base_url' => 'http://localhost/wifi-billing/public',
    'app_key' => 'YOUR_SECRET_KEY',

    // M-Pesa Daraja
    'mpesa_consumer_key' => 'YOUR_CONSUMER_KEY',
    'mpesa_consumer_secret' => 'YOUR_CONSUMER_SECRET',
    'mpesa_shortcode' => '174379', 
    'mpesa_passkey' => 'YOUR_PASSKEY'
];
