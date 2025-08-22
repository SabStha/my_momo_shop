<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Momo Shop Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the Momo Shop application.
    |
    */

    'currency' => 'NPR',
    'currency_symbol' => 'Rs.',
    
    'order_statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ],

    'payment_statuses' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed'
    ],

    'product_categories' => [
        'Chicken' => 'Chicken Momos',
        'Vegetarian' => 'Vegetarian Momos',
        'Pork' => 'Pork Momos',
        'Beef' => 'Beef Momos'
    ]
]; 