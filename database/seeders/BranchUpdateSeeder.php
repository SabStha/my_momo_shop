<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BranchUpdate;
use App\Models\Branch;
use Carbon\Carbon;

class BranchUpdateSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Please seed branches first.');
            return;
        }

        $updateTemplates = [
            [
                'type' => 'sales_update',
                'title' => '{branch} hit 150+ orders this week! 🎉',
                'content' => 'Strong performance with excellent customer feedback and repeat orders.',
                'icon' => '📈',
            ],
            [
                'type' => 'promo_update',
                'title' => 'New promotion launched at {branch}',
                'content' => 'Buy 2 Get 1 Free on all steamed momos. Limited time offer!',
                'icon' => '🎯',
            ],
            [
                'type' => 'review_highlight',
                'title' => 'Customer Review Highlight',
                'content' => '"Best momos in town! The service is amazing and the food is always fresh."',
                'icon' => '⭐',
            ],
            [
                'type' => 'milestone',
                'title' => '{branch} celebrates 1000th customer!',
                'content' => 'We reached a major milestone today. Thank you to all our loyal customers!',
                'icon' => '🎊',
            ],
            [
                'type' => 'announcement',
                'title' => 'New menu items added',
                'content' => 'Introducing our spicy schezwan momos and chocolate momos. Come try them today!',
                'icon' => '🔥',
            ],
            [
                'type' => 'sales_update',
                'title' => 'Weekend sales exceeded expectations',
                'content' => '{branch} saw a 40% increase in weekend sales compared to last month.',
                'icon' => '💰',
            ],
        ];

        foreach ($branches as $branch) {
            // Create 3-5 random updates per branch
            $numUpdates = rand(3, 5);
            
            for ($i = 0; $i < $numUpdates; $i++) {
                $template = $updateTemplates[array_rand($updateTemplates)];
                $daysAgo = rand(1, 30);
                
                BranchUpdate::create([
                    'branch_id' => $branch->id,
                    'type' => $template['type'],
                    'title' => str_replace('{branch}', $branch->name, $template['title']),
                    'content' => str_replace('{branch}', $branch->name, $template['content']),
                    'icon' => $template['icon'],
                    'is_published' => true,
                    'published_at' => Carbon::now()->subDays($daysAgo),
                    'created_by' => 1, // Assuming admin user ID is 1
                ]);
            }
        }

        $this->command->info('Branch updates seeded successfully!');
    }
}
