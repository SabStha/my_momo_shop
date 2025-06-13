<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Printer Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the receipt printer and cash drawer.
    |
    */

    'type' => env('PRINTER_TYPE', 'usb'), // 'usb' or 'network'
    
    // USB Printer Settings
    'path' => env('PRINTER_PATH', '/dev/usb/lp0'), // Linux USB path
    
    // Network Printer Settings
    'ip' => env('PRINTER_IP', '192.168.1.100'),
    'port' => env('PRINTER_PORT', 9100),
    
    // Printer Settings
    'width' => env('PRINTER_WIDTH', 32), // Characters per line
    'characterset' => env('PRINTER_CHARSET', 'CP437'), // Character set
]; 