<?php

namespace App\Helpers;

class CspHelper
{
    public static function nonce()
    {
        return base64_encode(random_bytes(16));
    }
} 