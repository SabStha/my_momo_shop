<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Creator;
use App\Models\Referral;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateReferralTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'referral:generate-test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake creators, users, referrals, and orders for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up old test data...');
        \App\Models\User::where('email', 'like', 'user%@example.com')->delete();
        \App\Models\User::where('email', 'like', 'creator%@example.com')->delete();
        $this->info('Generating test data...');
        DB::beginTransaction();
        try {
            // 1. Create 10 fake creators
            $creators = collect();
            for ($i = 1; $i <= 10; $i++) {
                $user = User::create([
                    'name' => 'Creator ' . $i,
                    'email' => 'creator' . $i . '@example.com',
                    'password' => Hash::make('password'),
                    'is_creator' => true,
                ]);
                $code = Str::random(8);
                $creator = Creator::create([
                    'user_id' => $user->id,
                    'code' => $code,
                    'bio' => 'Test creator ' . $i,
                    'points' => 0,
                    'referral_count' => 0,
                    'earnings' => 0,
                ]);
                $creators->push($creator);
            }

            // 2. Create 100 fake users and assign to random creators
            for ($i = 1; $i <= 100; $i++) {
                $user = User::create([
                    'name' => 'User ' . $i,
                    'email' => 'user' . $i . '@example.com',
                    'password' => Hash::make('password'),
                ]);
                $creator = $creators->random();
                // 3. Create referral
                $referral = Referral::create([
                    'code' => $creator->code,
                    'referred_user_id' => $user->id,
                    'creator_id' => $creator->id,
                    'status' => 'signed_up',
                    'order_count' => 0,
                ]);
                // Give 10 points for signup
                $creator->points += 10;
                $creator->referral_count += 1;
                $creator->save();
                // 4. Simulate 1-10 orders per referred user
                $orderCount = rand(1, 10);
                for ($j = 1; $j <= $orderCount; $j++) {
                    $orderDate = Carbon::now()->subDays(rand(0, 60))->subMinutes(rand(0, 1440));
                    Order::create([
                        'user_id' => $user->id,
                        'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                        'type' => 'online',
                        'status' => 'completed',
                        'payment_status' => 'paid',
                        'payment_method' => 'cash',
                        'amount_received' => 100,
                        'change' => 0,
                        'total_amount' => 100,
                        'tax_amount' => 13,
                        'grand_total' => 113,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);
                    // Give 5 points per order (up to 10 orders)
                    if ($j <= 10) {
                        $creator->points += 5;
                    }
                }
                $referral->status = 'ordered';
                $referral->order_count = $orderCount;
                $referral->save();
                $creator->save();
            }
            DB::commit();
            $this->info('Test data generated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
