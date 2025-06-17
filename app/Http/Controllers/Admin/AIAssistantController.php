<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Segment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AIAssistantController extends Controller
{
    public function handleRequest(Request $request)
    {
        $question = $request->input('question');
        $context = $request->input('context', []);
        
        // Extract context variables
        $startDate = $context['start_date'] ?? now()->subMonths(3)->format('Y-m-d');
        $endDate = $context['end_date'] ?? now()->format('Y-m-d');
        $branchId = $context['branch_id'] ?? 1;

        // Process the question and generate response
        $response = $this->processQuestion($question, $startDate, $endDate, $branchId);

        return response()->json([
            'status' => 'success',
            'response' => $response['answer'],
            'suggestions' => $response['suggestions'] ?? []
        ]);
    }

    private function processQuestion($question, $startDate, $endDate, $branchId)
    {
        // Convert question to lowercase for easier matching
        $question = strtolower($question);

        // Handle retention questions
        if (str_contains($question, 'who should i retain') || str_contains($question, 'retention')) {
            return $this->handleRetentionQuestion($startDate, $endDate, $branchId);
        }

        // Handle campaign questions
        if (str_contains($question, 'campaign') || str_contains($question, 'promotion')) {
            return $this->handleCampaignQuestion($startDate, $endDate, $branchId);
        }

        // Handle segment analysis questions
        if (str_contains($question, 'segment') || str_contains($question, 'analyze')) {
            return $this->handleSegmentAnalysis($startDate, $endDate, $branchId);
        }

        // Default response for unrecognized questions
        return [
            'answer' => "I'm not sure how to help with that specific question. You can ask me about customer retention, campaign ideas, or segment analysis.",
            'suggestions' => [
                'Who should I retain this week?',
                'Give me campaign ideas',
                'Analyze customer segments'
            ]
        ];
    }

    private function handleRetentionQuestion($startDate, $endDate, $branchId)
    {
        // Get high-risk customers
        $highRiskCustomers = Customer::where('branch_id', $branchId)
            ->where('last_order_at', '<', now()->subDays(30))
            ->where('total_orders', '>', 0)
            ->orderBy('total_spent', 'desc')
            ->take(5)
            ->get();

        $response = "Here are the top customers at risk of churning:\n\n";
        
        foreach ($highRiskCustomers as $customer) {
            $daysSinceLastOrder = now()->diffInDays($customer->last_order_at);
            $response .= "• {$customer->name} - Last order {$daysSinceLastOrder} days ago, spent \${$customer->total_spent}\n";
        }

        return [
            'answer' => $response,
            'suggestions' => [
                'Create a retention campaign for these customers',
                'Show me their purchase history',
                'What are their favorite products?'
            ]
        ];
    }

    private function handleCampaignQuestion($startDate, $endDate, $branchId)
    {
        // Get customer segments
        $segments = Segment::where('branch_id', $branchId)->get();
        
        $response = "Here are some campaign ideas based on your customer segments:\n\n";
        
        foreach ($segments as $segment) {
            $response .= "• {$segment->name} Segment:\n";
            $response .= "  - " . $this->generateCampaignIdea($segment) . "\n";
        }

        return [
            'answer' => $response,
            'suggestions' => [
                'Show me more campaign ideas',
                'Create a campaign for VIP customers',
                'What is the best time to run campaigns?'
            ]
        ];
    }

    private function handleSegmentAnalysis($startDate, $endDate, $branchId)
    {
        // Get segment statistics
        $segments = Segment::where('branch_id', $branchId)
            ->withCount('customers')
            ->withSum('customers', 'total_spent')
            ->get();

        $response = "Here's an analysis of your customer segments:\n\n";
        
        foreach ($segments as $segment) {
            $avgSpent = $segment->customers_count > 0 
                ? round($segment->customers_sum_total_spent / $segment->customers_count, 2)
                : 0;
            
            $response .= "• {$segment->name}:\n";
            $response .= "  - Customers: {$segment->customers_count}\n";
            $response .= "  - Average Spent: \${$avgSpent}\n";
        }

        return [
            'answer' => $response,
            'suggestions' => [
                'Show me segment growth trends',
                'Compare segment performance',
                'What is the best performing segment?'
            ]
        ];
    }

    private function generateCampaignIdea($segment)
    {
        $ideas = [
            'VIP' => 'Exclusive early access to new products with 20% discount',
            'Loyal' => 'Buy one get one free on their favorite category',
            'Regular' => '15% off on their next purchase',
            'New' => 'Welcome package with 25% off first order',
            'At-Risk' => 'Special comeback offer with 30% discount'
        ];

        return $ideas[$segment->name] ?? 'Personalized offer based on purchase history';
    }
} 