<?php

if (!function_exists('csp_nonce')) {
    function csp_nonce()
    {
        return \App\Helpers\CspHelper::nonce();
    }
}

if (!function_exists('checkCashDrawerAlerts')) {
    /**
     * Check cash drawer for low change and excess cash alerts.
     *
     * @param array $cashDrawer [denomination => amount]
     * @param array|null $config Optional config override
     * @return array ['low_change' => bool, 'excess_cash' => bool]
     */
    function checkCashDrawerAlerts($cashDrawer, $config = null)
    {
        $config = $config ?? config('cash_drawer');
        $smallDenoms = $config['small_denominations'];
        $largeDenoms = $config['large_denominations'];

        $lowChangeThreshold = $config['low_change_threshold'];
        $excessCashThreshold = $config['excess_cash_threshold'];
        $largeDenomThreshold = $config['large_denomination_threshold'];

        $smallTotal = 0;
        $largeTotal = 0;
        $overallTotal = 0;

        foreach ($cashDrawer as $denom => $amount) {
            $denom = (int)$denom;
            $overallTotal += $amount;
            if (in_array($denom, $smallDenoms)) {
                $smallTotal += $amount;
            }
            if (in_array($denom, $largeDenoms)) {
                $largeTotal += $amount;
            }
        }

        $alerts = [
            'low_change' => $smallTotal < $lowChangeThreshold,
            'excess_cash' => $overallTotal > $excessCashThreshold || $largeTotal > $largeDenomThreshold,
        ];

        // Optionally: log or fire events here if needed
        // Example: \Log::info('Cash Drawer Alert', ['alerts' => $alerts, 'drawer' => $cashDrawer]);

        return $alerts;
    }
}

// Settings Helper Functions
if (!function_exists('getCurrencySymbol')) {
    function getCurrencySymbol()
    {
        return \App\Models\SiteSetting::getValue('currency_symbol', config('momo.currency_symbol', 'Rs.'));
    }
}

if (!function_exists('getCurrencyCode')) {
    function getCurrencyCode()
    {
        return \App\Models\SiteSetting::getValue('currency_code', config('momo.currency', 'NPR'));
    }
}

if (!function_exists('getTaxRate')) {
    function getTaxRate()
    {
        return \App\Models\SiteSetting::getValue('tax_rate', 13);
    }
}

if (!function_exists('getDeliveryFee')) {
    function getDeliveryFee()
    {
        return \App\Models\SiteSetting::getValue('delivery_fee', 5.00);
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($amount, $decimals = 2)
    {
        $symbol = getCurrencySymbol();
        return $symbol . number_format($amount, $decimals);
    }
}

if (!function_exists('getLoyaltyPointsValue')) {
    function getLoyaltyPointsValue()
    {
        return \App\Models\SiteSetting::getValue('loyalty_points_value', 5);
    }
}

if (!function_exists('getLoyaltyPointsRequired')) {
    function getLoyaltyPointsRequired()
    {
        return \App\Models\SiteSetting::getValue('loyalty_points_required', 100);
    }
}

if (!function_exists('getMinimumOrderAmount')) {
    function getMinimumOrderAmount()
    {
        return \App\Models\SiteSetting::getValue('minimum_order_amount', 1000);
    }
}

if (!function_exists('getMaxDiscountPercentage')) {
    function getMaxDiscountPercentage()
    {
        return \App\Models\SiteSetting::getValue('max_discount_percentage', 50);
    }
} 