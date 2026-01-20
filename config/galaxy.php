<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Galaxy Ticketing System Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Galaxy ticketing system integration
    |
    */

    'api_endpoint' => env('GALAXY_API_ENDPOINT', 'https://galaxy-api.example.com'),
    
    'api_key' => env('GALAXY_API_KEY', ''),
    
    'api_secret' => env('GALAXY_API_SECRET', ''),
    
    // Timeout for API requests (seconds)
    'timeout' => env('GALAXY_TIMEOUT', 30),
    
    // Retry configuration
    'retry_times' => 3,
    'retry_delay' => 1000, // milliseconds

];
