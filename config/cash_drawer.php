<?php
return [
    'low_change_threshold' => 500,
    'excess_cash_threshold' => 8000,
    'large_denomination_threshold' => 7000,
    'small_denominations' => [1, 2, 5, 10, 20, 50, 100],
    'large_denominations' => [500, 1000],
    'notify_emails' => env('CASH_DRAWER_NOTIFY_EMAILS', 'sabstha98@gmail.com'),
]; 