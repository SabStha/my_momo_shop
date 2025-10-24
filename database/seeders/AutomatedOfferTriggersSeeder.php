<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AutomatedOfferTrigger;

class AutomatedOfferTriggersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $triggers = [
            [
                'name' => 'Welcome Offer - First Order',
                'trigger_type' => 'new_user_welcome',
                'description' => 'Send welcome offer to new users 24 hours after their first order',
                'conditions' => ['orders_count' => 1, 'hours_after_first_order' => 24],
                'offer_template' => [
                    'title' => 'Welcome Back! 15% OFF Your Next Order',
                    'description' => 'Thank you for trying our momos! Enjoy 15% OFF on your next order as our way of saying thanks.',
                    'discount' => 15,
                    'min_purchase' => 20,
                    'valid_days' => 7,
                ],
                'priority' => 10,
                'is_active' => true,
                'max_uses_per_user' => 1,
                'cooldown_days' => 999, // Once per user
            ],
            [
                'name' => 'Inactive User Win-Back (14 Days)',
                'trigger_type' => 'inactive_user',
                'description' => 'Re-engage users who haven\'t ordered in 14 days',
                'conditions' => ['days_inactive' => 14],
                'offer_template' => [
                    'title' => 'We Miss You! 20% OFF to Come Back',
                    'description' => 'It has been a while since your last visit. Come back and enjoy 20% OFF on us!',
                    'discount' => 20,
                    'min_purchase' => 25,
                    'valid_days' => 5,
                ],
                'priority' => 8,
                'is_active' => true,
                'max_uses_per_user' => null,
                'cooldown_days' => 30,
            ],
            [
                'name' => 'Inactive User Win-Back (30 Days)',
                'trigger_type' => 'inactive_user',
                'description' => 'Aggressive win-back for users inactive 30+ days',
                'conditions' => ['days_inactive' => 30],
                'offer_template' => [
                    'title' => 'Come Back! 25% OFF Special Offer',
                    'description' => 'We really miss you! Here is an exclusive 25% discount to welcome you back.',
                    'discount' => 25,
                    'min_purchase' => 20,
                    'valid_days' => 7,
                ],
                'priority' => 9,
                'is_active' => true,
                'max_uses_per_user' => null,
                'cooldown_days' => 45,
            ],
            [
                'name' => 'Birthday Special',
                'trigger_type' => 'birthday',
                'description' => 'Send birthday offer on user\'s birthday',
                'conditions' => ['birthday_match' => true],
                'offer_template' => [
                    'title' => 'Happy Birthday! 25% OFF',
                    'description' => 'Celebrate your special day with us! Enjoy 25% OFF on any order today.',
                    'discount' => 25,
                    'min_purchase' => 15,
                    'valid_days' => 3,
                ],
                'priority' => 9,
                'is_active' => true,
                'max_uses_per_user' => 1,
                'cooldown_days' => 365, // Once per year
            ],
            [
                'name' => 'VIP Exclusive - Monthly Reward',
                'trigger_type' => 'high_value_vip',
                'description' => 'Monthly exclusive offer for customers who spent 5000+ total',
                'conditions' => ['min_lifetime_value' => 5000],
                'offer_template' => [
                    'title' => 'VIP Exclusive: 20% OFF for You',
                    'description' => 'As a valued VIP customer, enjoy this exclusive monthly reward. Thank you for your loyalty!',
                    'discount' => 20,
                    'min_purchase' => 30,
                    'valid_days' => 10,
                ],
                'priority' => 7,
                'is_active' => true,
                'max_uses_per_user' => null,
                'cooldown_days' => 30, // Once per month
            ],
        ];

        foreach ($triggers as $triggerData) {
            AutomatedOfferTrigger::updateOrCreate(
                ['trigger_type' => $triggerData['trigger_type'], 'name' => $triggerData['name']],
                $triggerData
            );
        }

        $this->command->info('✅ Created ' . count($triggers) . ' automated offer triggers');
    }
}
