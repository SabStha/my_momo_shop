<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerSegment;

class CustomerSegmentSeeder extends Seeder
{
    public function run()
    {
        CustomerSegment::create([
            'branch_id' => 1,
            'name' => 'VIP Customers',
            'description' => 'High-value, loyal customers',
            'criteria' => json_encode(['total_spent' => '>=1000', 'orders' => '>=10']),
            'customer_count' => 0,
            'average_value' => 0,
            'is_active' => true,
        ]);
        CustomerSegment::create([
            'branch_id' => 1,
            'name' => 'At-Risk Customers',
            'description' => 'Customers who have not purchased in 60+ days',
            'criteria' => json_encode(['days_since_last_order' => '>=60']),
            'customer_count' => 0,
            'average_value' => 0,
            'is_active' => true,
        ]);
        CustomerSegment::create([
            'branch_id' => 1,
            'name' => 'Churned Customers',
            'description' => 'Customers who have not purchased in 90+ days',
            'criteria' => json_encode(['days_since_last_order' => '>=90']),
            'customer_count' => 0,
            'average_value' => 0,
            'is_active' => true,
        ]);
    }
} 