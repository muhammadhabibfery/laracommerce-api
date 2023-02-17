<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | This values this required for requirement midtrans configuration.
    |
    */

    'midtrans_serverkey' => env('MIDTRANS_SERVER_KEY', null),
    'midtrans_production' => env('MIDTRANS_PRODUCTION', false),
    'midtrans_sanitized' => env('MIDTRANS_SANITIZED', true),
    'midtrans_3ds' => env('MIDTRANS_3DS', true)

];
