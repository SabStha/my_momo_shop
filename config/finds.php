<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Finds Page Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the dynamic configuration for the Ama's Finds page.
    | All text, labels, and settings can be modified here without touching the code.
    |
    */

    // Page title and subtitle
    'title' => env('FINDS_TITLE', 'Ama\'s Finds'),
    'subtitle' => env('FINDS_SUBTITLE', 'Buy some, earn others — welcome to Ama\'s Finds'),

    // Button and badge text
    'add_to_cart_text' => env('FINDS_ADD_TO_CART_TEXT', '🛒 Add to Cart'),
    'unlockable_text' => env('FINDS_UNLOCKABLE_TEXT', '🎁 Unlockable'),
    'urgency_badge_text' => env('FINDS_URGENCY_BADGE_TEXT', '🔥 Buy Now'),
    'earn_badge_text' => env('FINDS_EARN_BADGE_TEXT', '🎁 Earn It'),

    // Messages and tooltips
    'progress_message' => env('FINDS_PROGRESS_MESSAGE', '🔥 You\'re 1 meal away from unlocking this!'),
    'earn_tooltip_message' => env('FINDS_EARN_TOOLTIP_MESSAGE', 'Unlock this by ordering the Couple Combo meal this month!'),

    // Category configurations
    'categories' => [
        'buyable' => [
            'label' => env('FINDS_CATEGORY_BUYABLE_LABEL', 'BUY'),
            'icon' => env('FINDS_CATEGORY_BUYABLE_ICON', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"/></svg>'),
            'description' => env('FINDS_CATEGORY_BUYABLE_DESCRIPTION', 'Items you can purchase directly'),
        ],
        'unlockable' => [
            'label' => env('FINDS_CATEGORY_UNLOCKABLE_LABEL', 'EARN'),
            'icon' => env('FINDS_CATEGORY_UNLOCKABLE_ICON', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>'),
            'description' => env('FINDS_CATEGORY_UNLOCKABLE_DESCRIPTION', 'Items you can earn by completing meals'),
        ],
        'tshirts' => [
            'label' => env('FINDS_CATEGORY_TSHIRTS_LABEL', 'SHIRT'),
            'icon' => env('FINDS_CATEGORY_TSHIRTS_ICON', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'),
            'description' => env('FINDS_CATEGORY_TSHIRTS_DESCRIPTION', 'Exclusive t-shirts and apparel'),
        ],
        'accessories' => [
            'label' => env('FINDS_CATEGORY_ACCESSORIES_LABEL', 'GIFT'),
            'icon' => env('FINDS_CATEGORY_ACCESSORIES_ICON', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>'),
            'description' => env('FINDS_CATEGORY_ACCESSORIES_DESCRIPTION', 'Accessories and gift items'),
        ],
        'toys' => [
            'label' => env('FINDS_CATEGORY_TOYS_LABEL', 'TOYS'),
            'icon' => env('FINDS_CATEGORY_TOYS_ICON', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>'),
            'description' => env('FINDS_CATEGORY_TOYS_DESCRIPTION', 'Fun toys and collectibles'),
        ],
        'limited' => [
            'label' => env('FINDS_CATEGORY_LIMITED_LABEL', 'LIM'),
            'icon' => env('FINDS_CATEGORY_LIMITED_ICON', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'),
            'description' => env('FINDS_CATEGORY_LIMITED_DESCRIPTION', 'Limited edition items'),
        ],
    ],

    // Color schemes (can be customized)
    'colors' => [
        'primary' => env('FINDS_PRIMARY_COLOR', '#6E0D25'),
        'secondary' => env('FINDS_SECONDARY_COLOR', '#B91C1C'),
        'background' => env('FINDS_BACKGROUND_COLOR', '#F4E9E1'),
        'text_primary' => env('FINDS_TEXT_PRIMARY_COLOR', '#6E0D25'),
        'text_secondary' => env('FINDS_TEXT_SECONDARY_COLOR', '#374151'),
    ],

    // Animation settings
    'animations' => [
        'enabled' => env('FINDS_ANIMATIONS_ENABLED', true),
        'duration' => env('FINDS_ANIMATION_DURATION', 300),
        'delay' => env('FINDS_ANIMATION_DELAY', 100),
    ],

    // Mobile-specific settings
    'mobile' => [
        'grid_columns' => env('FINDS_MOBILE_GRID_COLUMNS', 2),
        'image_height' => env('FINDS_MOBILE_IMAGE_HEIGHT', 'h-64 sm:h-72'),
        'touch_feedback' => env('FINDS_MOBILE_TOUCH_FEEDBACK', true),
    ],

    // Feature toggles
    'features' => [
        'wishlist' => env('FINDS_WISHLIST_ENABLED', true),
        'progress_indicators' => env('FINDS_PROGRESS_INDICATORS_ENABLED', true),
        'urgency_badges' => env('FINDS_URGENCY_BADGES_ENABLED', true),
        'earn_tooltips' => env('FINDS_EARN_TOOLTIPS_ENABLED', true),
        'image_overlays' => env('FINDS_IMAGE_OVERLAYS_ENABLED', true),
    ],
]; 