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