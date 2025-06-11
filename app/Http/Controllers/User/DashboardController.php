<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            $totalSales = \App\Models\Order::where('status', 'completed')->sum('total');
            $totalOrdersReport = \App\Models\Order::where('status', 'completed')->count();
            $totalRevenue = $totalSales;
            $totalCost = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.status', 'completed')
                ->sum(DB::raw('order_items.quantity * products.cost_price'));
            $totalProfit = $totalRevenue - $totalCost;
            $employeeHours = \App\Models\TimeLog::select('employee_id',
                    DB::raw('SUM(TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW()))) as totalHours'),
                    DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW())) > 8 THEN TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW())) - 8 ELSE 0 END) as overtime')
                )
                ->groupBy('employee_id')
                ->get()
                ->map(function ($row) {
                    $employee = \App\Models\Employee::with('user')->find($row->employee_id);
                    $userName = $employee && $employee->user ? $employee->user->name : 'Unknown Employee';
                    $hourlyRate = 500;
                    $overtimeRate = $hourlyRate * 1.5;
                    $regularHours = $row->totalHours - $row->overtime;
                    $totalPay = ($regularHours * $hourlyRate) + ($row->overtime * $overtimeRate);
                    return [
                        'name' => $userName,
                        'totalHours' => $row->totalHours,
                        'overtime' => $row->overtime,
                        'totalPay' => $totalPay
                    ];
                });
            $profitAnalysis = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $revenue = \App\Models\Order::where('status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('total');
                $cost = \App\Models\OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('orders.status', 'completed')
                    ->whereDate('orders.created_at', $date)
                    ->sum(DB::raw('order_items.quantity * products.cost_price'));
                $profit = $revenue - $cost;
                $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
                $profitAnalysis[] = [
                    'date' => $date,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'margin' => $margin
                ];
            }
            $recentOrders = \App\Models\Order::with('user')->latest()->take(5)->get();
            $topProducts = \App\Models\Product::select('products.*', DB::raw('SUM(order_items.quantity) as sold_count'))
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'completed')
                ->groupBy('products.id')
                ->orderBy('sold_count', 'desc')
                ->take(5)
                ->get();
            return view('admin.dashboard', compact('user', 'totalSales', 'totalOrdersReport', 'totalProfit', 'employeeHours', 'profitAnalysis', 'recentOrders', 'topProducts'));
        }
        return view('home', compact('user'));
    }
} 