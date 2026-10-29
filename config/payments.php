<?php

return [

    'default' => env('PAYMENTS_DEFAULT_PROVIDER', 'asaas'),

    'fee' => [
        'percent' => env('PAYMENTS_FEE_PERCENT', 5),
        'fixed_cents' => env('PAYMENTS_FEE_FIXED_CENTS', 0),
    ],

    'providers' => [

        'asaas' => [
            'base_url' => [
                'sandbox' => 'https://api-sandbox.asaas.com/v3',
                'production' => 'https://api.asaas.com/v3',
            ],
        ],

    ],

];
