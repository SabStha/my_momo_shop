<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Contact Information
            [
                'key' => 'phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'tel',
                'group' => 'contact',
                'label' => 'Phone Number',
                'description' => 'Main contact phone number',
                'is_active' => true,
            ],
            [
                'key' => 'email',
                'value' => 'info@amakoshop.com',
                'type' => 'email',
                'group' => 'contact',
                'label' => 'Email Address',
                'description' => 'Main contact email address',
                'is_active' => true,
            ],
            [
                'key' => 'address',
                'value' => 'Thamel, Kathmandu, Nepal',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Address',
                'description' => 'Restaurant physical address',
                'is_active' => true,
            ],
            [
                'key' => 'restaurant_name',
                'value' => 'Amako Momo Restaurant',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Restaurant Name',
                'description' => 'Official restaurant name',
                'is_active' => true,
            ],
            [
                'key' => 'restaurant_tagline',
                'value' => 'Find us and get in touch',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Restaurant Tagline',
                'description' => 'Tagline for restaurant section',
                'is_active' => true,
            ],

            // Business Hours
            [
                'key' => 'business_hours_days',
                'value' => 'Open 7 days a week',
                'type' => 'text',
                'group' => 'business',
                'label' => 'Business Hours Days',
                'description' => 'Business days description',
                'is_active' => true,
            ],
            [
                'key' => 'business_hours_time',
                'value' => '10:00 AM - 9:00 PM',
                'type' => 'text',
                'group' => 'business',
                'label' => 'Operating Hours',
                'description' => 'Daily operating hours',
                'is_active' => true,
            ],
            [
                'key' => 'business_status',
                'value' => '🟢 Currently Open',
                'type' => 'text',
                'group' => 'business',
                'label' => 'Current Status',
                'description' => 'Current business status',
                'is_active' => true,
            ],

            // Social Media Links
            [
                'key' => 'facebook_url',
                'value' => 'https://facebook.com/amakomomo',
                'type' => 'url',
                'group' => 'social',
                'label' => 'Facebook Page',
                'description' => 'Facebook page URL',
                'is_active' => true,
            ],
            [
                'key' => 'instagram_url',
                'value' => 'https://instagram.com/amakomomo',
                'type' => 'url',
                'group' => 'social',
                'label' => 'Instagram Profile',
                'description' => 'Instagram profile URL',
                'is_active' => true,
            ],
            [
                'key' => 'twitter_url',
                'value' => 'https://twitter.com/amakomomo',
                'type' => 'url',
                'group' => 'social',
                'label' => 'Twitter Profile',
                'description' => 'Twitter profile URL',
                'is_active' => true,
            ],
            [
                'key' => 'youtube_url',
                'value' => 'https://youtube.com/amakomomo',
                'type' => 'url',
                'group' => 'social',
                'label' => 'YouTube Channel',
                'description' => 'YouTube channel URL',
                'is_active' => true,
            ],
            [
                'key' => 'pinterest_url',
                'value' => 'https://pinterest.com/amakomomo',
                'type' => 'url',
                'group' => 'social',
                'label' => 'Pinterest Profile',
                'description' => 'Pinterest profile URL',
                'is_active' => true,
            ],
            [
                'key' => 'tiktok_url',
                'value' => 'https://tiktok.com/@amakomomo',
                'type' => 'url',
                'group' => 'social',
                'label' => 'TikTok Profile',
                'description' => 'TikTok profile URL',
                'is_active' => true,
            ],
            [
                'key' => 'messenger_url',
                'value' => 'https://m.me/amakomomo',
                'type' => 'url',
                'group' => 'social',
                'label' => 'Messenger Link',
                'description' => 'Facebook Messenger link',
                'is_active' => true,
            ],

            // General Settings
            [
                'key' => 'site_title',
                'value' => 'Amako Momo Shop',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Title',
                'description' => 'Main website title',
                'is_active' => true,
            ],
            [
                'key' => 'site_tagline',
                'value' => 'Authentic Nepalese Momos - Fresh, Delicious, and Made with Love',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Site Tagline',
                'description' => 'Website tagline or slogan',
                'is_active' => true,
            ],
            [
                'key' => 'site_description',
                'value' => 'The best momo restaurant in Kathmandu. Fresh ingredients, authentic recipes, fast delivery.',
                'type' => 'textarea',
                'group' => 'general',
                'label' => 'Site Description',
                'description' => 'Website description for SEO',
                'is_active' => true,
            ],
            [
                'key' => 'currency',
                'value' => 'NPR',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Currency',
                'description' => 'Default currency code',
                'is_active' => true,
            ],
            [
                'key' => 'currency_symbol',
                'value' => 'Rs.',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Currency Symbol',
                'description' => 'Currency symbol for display',
                'is_active' => true,
            ],

            // Tax & Delivery Settings
            [
                'key' => 'tax_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'tax_delivery',
                'label' => 'Enable Tax',
                'description' => 'Enable tax calculation',
                'is_active' => true,
            ],
            [
                'key' => 'tax_rate',
                'value' => '13',
                'type' => 'number',
                'group' => 'tax_delivery',
                'label' => 'Tax Rate (%)',
                'description' => 'Tax rate percentage',
                'is_active' => true,
            ],
            [
                'key' => 'delivery_fee_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'tax_delivery',
                'label' => 'Enable Delivery Fee',
                'description' => 'Enable delivery fee calculation',
                'is_active' => true,
            ],
            [
                'key' => 'delivery_fee_base',
                'value' => '50',
                'type' => 'number',
                'group' => 'tax_delivery',
                'label' => 'Base Delivery Fee (Rs.)',
                'description' => 'Base delivery fee amount',
                'is_active' => true,
            ],
            [
                'key' => 'delivery_fee_per_km',
                'value' => '10',
                'type' => 'number',
                'group' => 'tax_delivery',
                'label' => 'Delivery Fee per KM (Rs.)',
                'description' => 'Additional fee per kilometer',
                'is_active' => true,
            ],
            [
                'key' => 'free_delivery_threshold',
                'value' => '500',
                'type' => 'number',
                'group' => 'tax_delivery',
                'label' => 'Free Delivery Threshold (Rs.)',
                'description' => 'Minimum order amount for free delivery',
                'is_active' => true,
            ],
            [
                'key' => 'delivery_radius_km',
                'value' => '10',
                'type' => 'number',
                'group' => 'tax_delivery',
                'label' => 'Delivery Radius (KM)',
                'description' => 'Maximum delivery radius in kilometers',
                'is_active' => true,
            ],

            // Statistics Settings (Manual Override)
            [
                'key' => 'stats_orders_delivered',
                'value' => '',
                'type' => 'number',
                'group' => 'statistics',
                'label' => 'Orders Delivered Count',
                'description' => 'Number of orders delivered (leave empty for auto-calculation)',
                'is_active' => true,
            ],
            [
                'key' => 'stats_happy_customers',
                'value' => '',
                'type' => 'number',
                'group' => 'statistics',
                'label' => 'Happy Customers Count',
                'description' => 'Number of happy customers (leave empty for auto-calculation)',
                'is_active' => true,
            ],
            [
                'key' => 'stats_years_in_business',
                'value' => '',
                'type' => 'number',
                'group' => 'statistics',
                'label' => 'Years in Business Count',
                'description' => 'Number of years in business (leave empty for auto-calculation)',
                'is_active' => true,
            ],
            [
                'key' => 'stats_momo_varieties',
                'value' => '',
                'type' => 'number',
                'group' => 'statistics',
                'label' => 'Momo Varieties Count',
                'description' => 'Number of momo varieties (leave empty for auto-calculation)',
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}