<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class TaxDeliverySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tax Settings
        SiteSetting::setValue(
            'tax_rate',
            '13',
            'number',
            'tax_delivery',
            'Tax Rate (%)',
            'Percentage of tax applied to orders (e.g., 13 for 13%)'
        );

        SiteSetting::setValue(
            'tax_enabled',
            '1',
            'boolean',
            'tax_delivery',
            'Enable Tax',
            'Enable or disable tax calculation on orders'
        );

        // Delivery Fee Settings
        SiteSetting::setValue(
            'delivery_fee_base',
            '0',
            'number',
            'tax_delivery',
            'Base Delivery Fee (Rs.)',
            'Fixed delivery fee applied to all orders'
        );

        SiteSetting::setValue(
            'delivery_fee_per_km',
            '0',
            'number',
            'tax_delivery',
            'Delivery Fee per KM (Rs.)',
            'Additional delivery fee per kilometer distance'
        );

        SiteSetting::setValue(
            'delivery_fee_max',
            '100',
            'number',
            'tax_delivery',
            'Maximum Delivery Fee (Rs.)',
            'Maximum delivery fee that can be charged'
        );

        SiteSetting::setValue(
            'delivery_fee_enabled',
            '1',
            'boolean',
            'tax_delivery',
            'Enable Delivery Fee',
            'Enable or disable delivery fee calculation'
        );

        SiteSetting::setValue(
            'free_delivery_threshold',
            '500',
            'number',
            'tax_delivery',
            'Free Delivery Threshold (Rs.)',
            'Order amount above which delivery is free'
        );

        SiteSetting::setValue(
            'delivery_radius_km',
            '10',
            'number',
            'tax_delivery',
            'Delivery Radius (KM)',
            'Maximum delivery distance from branches'
        );
    }
} 