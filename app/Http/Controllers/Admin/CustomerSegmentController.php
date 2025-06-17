<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CustomerSegmentController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        return view('admin.customer-segments.index', compact('branches'));
    }

    public function exportSegments(Request $request)
    {
        $branchId = $request->input('branch_id');
        $segmentType = $request->input('segment_type', 'churned');
        $daysInactive = $request->input('days_inactive', 30);
        $format = $request->input('format', 'csv');

        $query = Customer::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Apply segment filters
        switch ($segmentType) {
            case 'churned':
                $query->whereDoesntHave('orders', function ($query) use ($daysInactive) {
                    $query->where('created_at', '>=', now()->subDays($daysInactive));
                })->whereHas('orders', function ($query) {
                    $query->where('created_at', '>=', now()->subDays(90));
                });
                break;

            case 'at-risk':
                $query->whereHas('orders', function ($query) {
                    $query->where('created_at', '>=', now()->subDays(90))
                        ->where('created_at', '<', now()->subDays(60));
                });
                break;

            case 'loyal':
                $query->whereHas('orders', function ($query) {
                    $query->where('created_at', '>=', now()->subDays(30));
                })->whereHas('orders', function ($query) {
                    $query->where('created_at', '>=', now()->subDays(90));
                }, '>=', 3);
                break;

            case 'new':
                $query->whereHas('orders', function ($query) {
                    $query->where('created_at', '>=', now()->subDays(30));
                })->whereDoesntHave('orders', function ($query) {
                    $query->where('created_at', '<', now()->subDays(30));
                });
                break;
        }

        $customers = $query->with([
            'orders' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
            'orders.items.product',
            'orders.items.product.category'
        ])->get();

        $data = $customers->map(function ($customer) {
            $lastOrder = $customer->orders->first();
            $totalSpent = $customer->orders->sum('total_amount');
            $averageOrderValue = $customer->orders->avg('total_amount');
            $daysSinceLastOrder = $lastOrder ? now()->diffInDays($lastOrder->created_at) : null;

            // Calculate customer lifetime value
            $lifetimeValue = $this->calculateLifetimeValue($customer);

            // Get customer preferences
            $preferences = $this->getCustomerPreferences($customer);

            return [
                'Customer ID' => $customer->id,
                'Name' => $customer->name,
                'Email' => $customer->email,
                'Phone' => $customer->phone,
                'Branch' => $customer->branch->name,
                'Segment' => $this->determineSegment($customer),
                'Total Orders' => $customer->orders->count(),
                'Total Spent' => number_format($totalSpent, 2),
                'Average Order Value' => number_format($averageOrderValue, 2),
                'Lifetime Value' => number_format($lifetimeValue, 2),
                'Last Order Date' => $lastOrder ? $lastOrder->created_at->format('Y-m-d') : 'N/A',
                'Days Since Last Order' => $daysSinceLastOrder,
                'Preferred Products' => $preferences['products'],
                'Preferred Categories' => $preferences['categories'],
                'Average Order Frequency' => $this->calculateOrderFrequency($customer),
                'Customer Since' => $customer->created_at->format('Y-m-d'),
                'Marketing Consent' => $customer->marketing_consent ? 'Yes' : 'No',
                'Preferred Payment Method' => $this->getPreferredPaymentMethod($customer),
                'Last Visit' => $lastOrder ? $lastOrder->created_at->format('Y-m-d H:i') : 'N/A',
                'Total Visits' => $customer->orders->count(),
                'Average Basket Size' => number_format($this->calculateAverageBasketSize($customer), 2),
                'Return Rate' => $this->calculateReturnRate($customer) . '%',
                'Engagement Score' => $this->calculateEngagementScore($customer),
            ];
        });

        if ($format === 'csv') {
            return $this->generateCsv($data, $segmentType);
        }

        return response()->json($data);
    }

    private function calculateLifetimeValue($customer)
    {
        $totalSpent = $customer->orders->sum('total_amount');
        $customerAge = now()->diffInDays($customer->created_at);
        return $customerAge > 0 ? ($totalSpent / $customerAge) * 365 : 0;
    }

    private function getCustomerPreferences($customer)
    {
        $products = $customer->orders()
            ->with('items.product')
            ->get()
            ->flatMap(function ($order) {
                return $order->items->pluck('product.name');
            })
            ->countBy()
            ->sortDesc()
            ->take(3)
            ->keys()
            ->implode(', ');

        $categories = $customer->orders()
            ->with('items.product.category')
            ->get()
            ->flatMap(function ($order) {
                return $order->items->pluck('product.category.name');
            })
            ->countBy()
            ->sortDesc()
            ->take(3)
            ->keys()
            ->implode(', ');

        return [
            'products' => $products,
            'categories' => $categories
        ];
    }

    private function determineSegment($customer)
    {
        $lastOrder = $customer->orders->first();
        $daysSinceLastOrder = $lastOrder ? now()->diffInDays($lastOrder->created_at) : null;
        $totalOrders = $customer->orders->count();

        if ($daysSinceLastOrder >= 30) {
            return 'Churned';
        } elseif ($daysSinceLastOrder >= 20) {
            return 'At Risk';
        } elseif ($totalOrders >= 3 && $daysSinceLastOrder < 30) {
            return 'Loyal';
        } elseif ($daysSinceLastOrder < 30) {
            return 'New';
        }

        return 'Inactive';
    }

    private function calculateOrderFrequency($customer)
    {
        $orders = $customer->orders->sortBy('created_at');
        if ($orders->count() < 2) {
            return 'N/A';
        }

        $firstOrder = $orders->first();
        $lastOrder = $orders->last();
        $daysBetween = $firstOrder->created_at->diffInDays($lastOrder->created_at);
        $orderCount = $orders->count();

        return $daysBetween > 0 ? round($daysBetween / ($orderCount - 1)) . ' days' : 'N/A';
    }

    private function getPreferredPaymentMethod($customer)
    {
        return $customer->orders()
            ->select('payment_method')
            ->get()
            ->pluck('payment_method')
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first() ?? 'N/A';
    }

    private function calculateAverageBasketSize($customer)
    {
        return $customer->orders->avg('total_amount') ?? 0;
    }

    private function calculateReturnRate($customer)
    {
        $totalOrders = $customer->orders->count();
        if ($totalOrders === 0) {
            return 0;
        }

        $returningOrders = $customer->orders()
            ->where('created_at', '>=', now()->subDays(90))
            ->count();

        return round(($returningOrders / $totalOrders) * 100);
    }

    private function calculateEngagementScore($customer)
    {
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
    }

    private function generateCsv($data, $segmentType)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="customer_segments_' . $segmentType . '_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, array_keys($data->first()));
            
            // Add data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 