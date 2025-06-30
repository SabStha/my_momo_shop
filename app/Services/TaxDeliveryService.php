<?php

namespace App\Services;

use App\Models\SiteSetting;

class TaxDeliveryService
{
    /**
     * Get tax rate from settings
     */
    public static function getTaxRate(): float
    {
        $taxEnabled = SiteSetting::getValue('tax_enabled', '1');
        if ($taxEnabled !== '1') {
            return 0;
        }
        
        return (float) SiteSetting::getValue('tax_rate', '13');
    }

    /**
     * Calculate tax amount
     */
    public static function calculateTax(float $subtotal): float
    {
        $taxRate = self::getTaxRate();
        return $subtotal * ($taxRate / 100);
    }

    /**
     * Check if delivery fee is enabled
     */
    public static function isDeliveryFeeEnabled(): bool
    {
        return SiteSetting::getValue('delivery_fee_enabled', '1') === '1';
    }

    /**
     * Get base delivery fee
     */
    public static function getBaseDeliveryFee(): float
    {
        return (float) SiteSetting::getValue('delivery_fee_base', '0');
    }

    /**
     * Get delivery fee per kilometer
     */
    public static function getDeliveryFeePerKm(): float
    {
        return (float) SiteSetting::getValue('delivery_fee_per_km', '0');
    }

    /**
     * Get maximum delivery fee
     */
    public static function getMaxDeliveryFee(): float
    {
        return (float) SiteSetting::getValue('delivery_fee_max', '100');
    }

    /**
     * Get free delivery threshold
     */
    public static function getFreeDeliveryThreshold(): float
    {
        return (float) SiteSetting::getValue('free_delivery_threshold', '500');
    }

    /**
     * Get delivery radius in kilometers
     */
    public static function getDeliveryRadius(): float
    {
        return (float) SiteSetting::getValue('delivery_radius_km', '10');
    }

    /**
     * Calculate delivery fee based on distance and order amount
     */
    public static function calculateDeliveryFee(float $distance = 0, float $orderAmount = 0): float
    {
        if (!self::isDeliveryFeeEnabled()) {
            return 0;
        }

        // Check if order qualifies for free delivery
        if ($orderAmount >= self::getFreeDeliveryThreshold()) {
            return 0;
        }

        $baseFee = self::getBaseDeliveryFee();
        $perKmFee = self::getDeliveryFeePerKm() * $distance;
        $totalFee = $baseFee + $perKmFee;
        $maxFee = self::getMaxDeliveryFee();

        return min($totalFee, $maxFee);
    }

    /**
     * Get all tax and delivery settings as array
     */
    public static function getAllSettings(): array
    {
        return [
            'tax_rate' => self::getTaxRate(),
            'tax_enabled' => self::getTaxRate() > 0,
            'delivery_fee_enabled' => self::isDeliveryFeeEnabled(),
            'base_delivery_fee' => self::getBaseDeliveryFee(),
            'delivery_fee_per_km' => self::getDeliveryFeePerKm(),
            'max_delivery_fee' => self::getMaxDeliveryFee(),
            'free_delivery_threshold' => self::getFreeDeliveryThreshold(),
            'delivery_radius' => self::getDeliveryRadius(),
        ];
    }
} 