<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contact Information
        SiteSetting::setValue('phone', '+1 (555) 123-4567', 'phone', 'contact', 'Phone Number', 'Main contact phone number');
        SiteSetting::setValue('email', 'info@amakoshop.com', 'email', 'contact', 'Email Address', 'Main contact email address');
        SiteSetting::setValue('address', 'Amako Momo Restaurant, Thamel, Kathmandu, Nepal', 'text', 'contact', 'Address', 'Restaurant address');
        
        // Business Hours
        SiteSetting::setValue('business_hours_days', 'Open 7 days a week', 'text', 'business', 'Business Days', 'Days of operation');
        SiteSetting::setValue('business_hours_time', '10:00 AM - 9:00 PM', 'text', 'business', 'Business Hours', 'Operating hours');
        SiteSetting::setValue('business_status', '🟢 Currently Open', 'text', 'business', 'Business Status', 'Current open/closed status');
        
        // Social Media Links
        SiteSetting::setValue('twitter_url', '#', 'url', 'social', 'Twitter URL', 'Twitter profile link');
        SiteSetting::setValue('instagram_url', '#', 'url', 'social', 'Instagram URL', 'Instagram profile link');
        SiteSetting::setValue('facebook_url', '#', 'url', 'social', 'Facebook URL', 'Facebook page link');
        SiteSetting::setValue('pinterest_url', '#', 'url', 'social', 'Pinterest URL', 'Pinterest profile link');
        SiteSetting::setValue('tiktok_url', '#', 'url', 'social', 'TikTok URL', 'TikTok profile link');
        SiteSetting::setValue('messenger_url', '#', 'url', 'social', 'Messenger URL', 'Facebook Messenger link');
        
        // Social Media Display Names
        SiteSetting::setValue('twitter_name', 'Twitter', 'text', 'social', 'Twitter Display Name', 'Display name for Twitter');
        SiteSetting::setValue('instagram_name', 'Instagram', 'text', 'social', 'Instagram Display Name', 'Display name for Instagram');
        SiteSetting::setValue('facebook_name', 'Facebook', 'text', 'social', 'Facebook Display Name', 'Display name for Facebook');
        SiteSetting::setValue('pinterest_name', 'Pinterest', 'text', 'social', 'Pinterest Display Name', 'Display name for Pinterest');
        
        // General Settings
        SiteSetting::setValue('restaurant_name', 'Amako Momo Restaurant', 'text', 'general', 'Restaurant Name', 'Name of the restaurant');
        SiteSetting::setValue('restaurant_tagline', 'Find us and get in touch', 'text', 'general', 'Restaurant Tagline', 'Tagline for the restaurant');
    }
}
