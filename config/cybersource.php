<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CyberSource Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for CyberSource payment gateway integration
    |
    */

    'merchant_id' => env('CYBERSOURCE_MERCHANT_ID', ''),
    
    'api_key' => env('CYBERSOURCE_API_KEY', ''),
    
    'shared_secret' => env('CYBERSOURCE_SHARED_SECRET', ''),
    
    'webhook_secret' => env('CYBERSOURCE_WEBHOOK_SECRET', ''),
    
    'profile_id' => env('CYBERSOURCE_PROFILE_ID', ''),
    
    // API Endpoint
    'api_endpoint' => env('CYBERSOURCE_API_ENDPOINT', 'https://api.cybersource.com'),
    
    // Secure Acceptance Payment URL
    'payment_url' => env('CYBERSOURCE_PAYMENT_URL', 'https://secureacceptance.cybersource.com/pay'),
    
    // Test mode
    'test_mode' => env('CYBERSOURCE_TEST_MODE', true),
    
    // Transaction types
    'transaction_type' => 'sale', // or 'authorization'
    
    // Currency
    'default_currency' => 'AED',
    
    // Locale
    'locale' => 'en',

];
