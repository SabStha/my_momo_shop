<?php

namespace App\Helpers;

use App\Models\SiteSetting;

class SettingsHelper
{
    /**
     * Get currency symbol from config or site settings
     */
    public static function getCurrencySymbol()
    {
        return SiteSetting::getValue('currency_symbol', config('momo.currency_symbol', 'Rs.'));
    }

    /**
     * Get currency code from config or site settings
     */
    public static function getCurrencyCode()
    {
        return SiteSetting::getValue('currency_code', config('momo.currency', 'NPR'));
    }

    /**
     * Get tax rate from site settings
     */
    public static function getTaxRate()
    {
        return SiteSetting::getValue('tax_rate', 13);
    }

    /**
     * Get delivery fee from site settings
     */
    public static function getDeliveryFee()
    {
        return SiteSetting::getValue('delivery_fee', 5.00);
    }

    /**
     * Format price with currency symbol
     */
    public static function formatPrice($amount, $decimals = 2)
    {
        $symbol = self::getCurrencySymbol();
        return $symbol . number_format($amount, $decimals);
    }

    /**
     * Get loyalty points value from site settings
     */
    public static function getLoyaltyPointsValue()
    {
        return SiteSetting::getValue('loyalty_points_value', 5);
    }

    /**
     * Get loyalty points required from site settings
     */
    public static function getLoyaltyPointsRequired()
    {
        return SiteSetting::getValue('loyalty_points_required', 100);
    }

    /**
     * Get minimum order amount from site settings
     */
    public static function getMinimumOrderAmount()
    {
        return SiteSetting::getValue('minimum_order_amount', 1000);
    }

    /**
     * Get maximum discount percentage from site settings
     */
    public static function getMaxDiscountPercentage()
    {
        return SiteSetting::getValue('max_discount_percentage', 50);
    }
} 