<?php

if (!function_exists('csp_nonce')) {
    function csp_nonce()
    {
        return \App\Helpers\CspHelper::nonce();
    }
} 