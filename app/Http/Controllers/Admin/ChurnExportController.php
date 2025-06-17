<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChurnExportController extends Controller
{
    public function exportChurnData(Request $request)
    {
        try {
            $branchId = $request->input('branch_id');
            $daysInactive = $request->input('days_inactive', 30);

            $query = User::query();

            if ($branchId) {
                $query->whereHas('orders', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }

            // Get churned customers
            $churnedCustomers = $query->whereDoesntHave('orders', function ($query) use ($daysInactive) {
                $query->where('created_at', '>=', now()->subDays($daysInactive));
            })->whereHas('orders', function ($query) {
                $query->where('created_at', '>=', now()->subDays(90));
            })->with(['orders' => function ($query) {
                $query->orderBy('created_at', 'desc')
                    ->with('branch');
            }])->get();

            // Define headers for the CSV
            $headers = [
                'Customer ID',
                'Name',
                'Email',
                'Phone',
                'Last Branch',
                'Total Orders',
                'Total Spent',
                'Average Order Value',
                'Last Order Date',
                'Days Since Last Order',
                'Customer Since',
                'Marketing Consent',
                'Preferred Payment Method',
                'Last Visit',
                'Total Visits',
                'Average Basket Size',
                'Return Rate',
                'Engagement Score'
            ];

            $data = [];
            if (!$churnedCustomers->isEmpty()) {
                $data = $churnedCustomers->map(function ($customer) {
                    $lastOrder = $customer->orders->first();
                    $totalSpent = $customer->orders->sum('total_amount');
                    $averageOrderValue = $customer->orders->avg('total_amount');
                    $daysSinceLastOrder = $lastOrder ? now()->diffInDays($lastOrder->created_at) : null;
                    $lastBranch = $lastOrder ? $lastOrder->branch : null;

                    return [
                        'Customer ID' => $customer->id,
                        'Name' => $customer->name,
                        'Email' => $customer->email,
                        'Phone' => $customer->phone,
                        'Last Branch' => $lastBranch ? $lastBranch->name : 'N/A',
                        'Total Orders' => $customer->orders->count(),
                        'Total Spent' => number_format($totalSpent, 2),
                        'Average Order Value' => number_format($averageOrderValue, 2),
                        'Last Order Date' => $lastOrder ? $lastOrder->created_at->format('Y-m-d') : 'N/A',
                        'Days Since Last Order' => $daysSinceLastOrder,
                        'Customer Since' => $customer->created_at->format('Y-m-d'),
                        'Marketing Consent' => $customer->marketing_consent ? 'Yes' : 'No',
                        'Preferred Payment Method' => $this->getPreferredPaymentMethod($customer),
                        'Last Visit' => $lastOrder ? $lastOrder->created_at->format('Y-m-d H:i') : 'N/A',
                        'Total Visits' => $customer->orders->count(),
                        'Average Basket Size' => number_format($this->calculateAverageBasketSize($customer), 2),
                        'Return Rate' => $this->calculateReturnRate($customer) . '%',
                        'Engagement Score' => $this->calculateEngagementScore($customer),
                    ];
                })->toArray();
            }

            $responseHeaders = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="churn_data_' . now()->format('Y-m-d') . '.csv"',
            ];

            $callback = function() use ($headers, $data) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, $headers);
                
                // Add data
                foreach ($data as $row) {
                    fputcsv($file, array_values($row));
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $responseHeaders);

        } catch (\Exception $e) {
            Log::error('Churn export error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Return error as CSV
            $responseHeaders = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="churn_data_error_' . now()->format('Y-m-d') . '.csv"',
            ];

            $callback = function() use ($e) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Error']);
                fputcsv($file, [$e->getMessage()]);
                fclose($file);
            };

            return response()->stream($callback, 500, $responseHeaders);
        }
    }

    private function getPreferredPaymentMethod($customer)
    {
        try {
            return $customer->orders()
                ->select('payment_method')
                ->get()
                ->pluck('payment_method')
                ->countBy()
                ->sortDesc()
                ->keys()
                ->first() ?? 'N/A';
        } catch (\Exception $e) {
            Log::error('Error getting preferred payment method: ' . $e->getMessage());
            return 'N/A';
        }
    }

    private function calculateAverageBasketSize($customer)
    {
        try {
            return $customer->orders->avg('total_amount') ?? 0;
        } catch (\Exception $e) {
            Log::error('Error calculating average basket size: ' . $e->getMessage());
            return 0;
        }
    }

    private function calculateReturnRate($customer)
    {
        try {
            $totalOrders = $customer->orders->count();
            if ($totalOrders === 0) {
                return 0;
            }

            $returningOrders = $customer->orders()
                ->where('created_at', '>=', now()->subDays(90))
                ->count();

            return round(($returningOrders / $totalOrders) * 100);
        } catch (\Exception $e) {
            Log::error('Error calculating return rate: ' . $e->getMessage());
            return 0;
        }
    }

    private function calculateEngagementScore($customer)
    {
        try {
            $score = 0;
            
            // Recent orders (last 30 days)
            $recentOrders = $customer->orders()
                ->where('created_at', '>=', now()->subDays(30))
                ->count();
            $score += $recentOrders * 10;

            // Order frequency
            $orderFrequency = $this->calculateOrderFrequency($customer);
            if (is_numeric($orderFrequency)) {
                $score += 20 - min($orderFrequency, 20);
            }

            // Average order value
            $avgOrderValue = $this->calculateAverageBasketSize($customer);
            $score += min($avgOrderValue / 10, 30);

            // Return rate
            $returnRate = $this->calculateReturnRate($customer);
            $score += min($returnRate, 40);

            return min($score, 100);
        } catch (\Exception $e) {
            Log::error('Error calculating engagement score: ' . $e->getMessage());
            return 0;
        }
    }

    private function calculateOrderFrequency($customer)
    {
        try {
            $orders = $customer->orders->sortBy('created_at');
            if ($orders->count() < 2) {
                return 'N/A';
            }

            $firstOrder = $orders->first();
            $lastOrder = $orders->last();
            $daysBetween = $firstOrder->created_at->diffInDays($lastOrder->created_at);
            $orderCount = $orders->count();

            return $daysBetween > 0 ? round($daysBetween / ($orderCount - 1)) : 'N/A';
        } catch (\Exception $e) {
            Log::error('Error calculating order frequency: ' . $e->getMessage());
            return 'N/A';
        }
    }
} 