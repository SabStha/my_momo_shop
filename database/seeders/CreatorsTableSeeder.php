<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Creator;

class CreatorsTableSeeder extends Seeder
{
    public function run()
    {
        Creator::create([
            'user_id' => 1,
            'code' => 'alice2024',
            'bio' => 'Alice is a top influencer in tech.',
            'avatar' => null,
            'referral_count' => 0,
        ]);
        Creator::create([
            'user_id' => 2,
            'code' => 'bob2024',
            'bio' => 'Bob shares the best deals online.',
            'avatar' => null,
            'referral_count' => 0,
        ]);
        Creator::create([
            'user_id' => 3,
            'code' => 'carol2024',
            'bio' => 'Carol is a lifestyle content creator.',
            'avatar' => null,
            'referral_count' => 0,
        ]);
    }
} 