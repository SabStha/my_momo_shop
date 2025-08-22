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
    'subtitle' => env('FINDS_SUBTITLE', '🎁 Some gifts you buy, some you earn. Welcome to Ama\'s Finds.'),

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
            'icon' => env('FINDS_CATEGORY_BUYABLE_ICON', '🛒'),
            'description' => env('FINDS_CATEGORY_BUYABLE_DESCRIPTION', 'Items you can purchase directly'),
        ],
        'unlockable' => [
            'label' => env('FINDS_CATEGORY_UNLOCKABLE_LABEL', 'EARN'),
            'icon' => env('FINDS_CATEGORY_UNLOCKABLE_ICON', '🎁'),
            'description' => env('FINDS_CATEGORY_UNLOCKABLE_DESCRIPTION', 'Items you can earn by completing meals'),
        ],
        'tshirts' => [
            'label' => env('FINDS_CATEGORY_TSHIRTS_LABEL', 'SHIRT'),
            'icon' => env('FINDS_CATEGORY_TSHIRTS_ICON', '👕'),
            'description' => env('FINDS_CATEGORY_TSHIRTS_DESCRIPTION', 'Exclusive t-shirts and apparel'),
        ],
        'accessories' => [
            'label' => env('FINDS_CATEGORY_ACCESSORIES_LABEL', 'GIFT'),
            'icon' => env('FINDS_CATEGORY_ACCESSORIES_ICON', '🎒'),
            'description' => env('FINDS_CATEGORY_ACCESSORIES_DESCRIPTION', 'Accessories and gift items'),
        ],
        'toys' => [
            'label' => env('FINDS_CATEGORY_TOYS_LABEL', 'TOYS'),
            'icon' => env('FINDS_CATEGORY_TOYS_ICON', '🧸'),
            'description' => env('FINDS_CATEGORY_TOYS_DESCRIPTION', 'Fun toys and collectibles'),
        ],
        'limited' => [
            'label' => env('FINDS_CATEGORY_LIMITED_LABEL', 'LIM'),
            'icon' => env('FINDS_CATEGORY_LIMITED_ICON', '🔥'),
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