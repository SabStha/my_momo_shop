<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $type = $request->input('type', 'sales');
        $dateRange = $this->getDateRange($request);

        switch ($type) {
            case 'sales':
                return $this->generateSalesReport($dateRange['start'], $dateRange['end']);
            case 'employee':
                return $this->generateEmployeeReport($dateRange['start'], $dateRange['end']);
            case 'products':
                return $this->generateProductReport($dateRange['start'], $dateRange['end']);
            case 'profit':
                return $this->generateProfitReport($dateRange['start'], $dateRange['end']);
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'sales');
        $format = $request->input('format', 'excel');
        $dateRange = $this->getDateRange($request);

        $data = $this->generate($request);
        $filename = "{$type}_report_" . date('Y-m-d');

        if ($format === 'excel') {
            return $this->exportToExcel($data, $filename);
        } else {
            return $this->exportToPDF($data, $filename);
        }
    }

    private function getDateRange(Request $request)
    {
        $range = $request->input('date_range', 'today');
        $start = null;
        $end = null;

        switch ($range) {
            case 'today':
                $start = Carbon::today();
                $end = Carbon::now();
                break;
            case 'yesterday':
                $start = Carbon::yesterday();
                $end = Carbon::yesterday()->endOfDay();
                break;
            case 'this_week':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now();
                break;
            case 'last_week':
                $start = Carbon::now()->subWeek()->startOfWeek();
                $end = Carbon::now()->subWeek()->endOfWeek();
                break;
            case 'this_month':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now();
                break;
            case 'last_month':
                $start = Carbon::now()->subMonth()->startOfMonth();
                $end = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                $start = Carbon::parse($request->input('start_date'));
                $end = Carbon::parse($request->input('end_date'));
                break;
        }

        return [
            'start' => $start,
            'end' => $end
        ];
    }

    private function generateSalesReport($start, $end)
    {
        // Get daily sales data
        $dailySales = Order::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as sales'),
                DB::raw('AVG(total_amount) as average')
            )
            ->groupBy('date')
            ->get()
            ->map(function ($item) {
                $item->paymentMethods = $this->getPaymentMethodsForDate($item->date);
                return $item;
            });

        // Get summary data
        $summary = Order::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('SUM(total_amount) as totalSales'),
                DB::raw('COUNT(*) as totalOrders'),
                DB::raw('AVG(total_amount) as averageOrderValue')
            )
            ->first();

        // Get payment methods breakdown
        $paymentMethods = Order::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        return response()->json([
            'dailySales' => $dailySales,
            'totalSales' => $summary->totalSales ?? 0,
            'totalOrders' => $summary->totalOrders ?? 0,
            'averageOrderValue' => $summary->averageOrderValue ?? 0,
            'paymentMethods' => $paymentMethods
        ]);
    }

    private function generateEmployeeReport($start, $end)
    {
        $employeeHours = TimeLog::whereBetween('clock_in', [$start, $end])
            ->select(
                'user_id',
                DB::raw('SUM(TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW()))) as total_hours'),
                DB::raw('SUM(CASE WHEN TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW())) > 8 
                    THEN TIMESTAMPDIFF(HOUR, clock_in, COALESCE(clock_out, NOW())) - 8 
                    ELSE 0 END) as overtime_hours')
            )
            ->groupBy('user_id')
            ->get()
            ->map(function ($log) {
                $user = User::find($log->user_id);
                $regularHours = $log->total_hours - $log->overtime_hours;
                $hourlyRate = 500; // Example hourly rate
                $overtimeRate = $hourlyRate * 1.5;
                
                return [
                    'id' => $log->user_id,
                    'name' => $user->name,
                    'totalHours' => $log->total_hours,
                    'regularHours' => $regularHours,
                    'overtimeHours' => $log->overtime_hours,
                    'totalPay' => ($regularHours * $hourlyRate) + ($log->overtime_hours * $overtimeRate)
                ];
            });

        return response()->json([
            'employeeHours' => $employeeHours
        ]);
    }

    private function generateProductReport($start, $end)
    {
        $productPerformance = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
                DB::raw('SUM(order_items.quantity * products.cost_price) as cost')
            )
            ->groupBy('products.id', 'products.name')
            ->get()
            ->map(function ($item) {
                $profit = $item->revenue - $item->cost;
                $profitMargin = $item->revenue > 0 ? ($profit / $item->revenue) * 100 : 0;
                
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'unitsSold' => $item->units_sold,
                    'revenue' => $item->revenue,
                    'cost' => $item->cost,
                    'profit' => $profit,
                    'profitMargin' => round($profitMargin, 2)
                ];
            });

        return response()->json([
            'productPerformance' => $productPerformance
        ]);
    }

    private function generateProfitReport($start, $end)
    {
        $profitAnalysis = Order::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->get()
            ->map(function ($item) {
                // Get cost of goods sold
                $costOfGoods = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('orders.status', 'completed')
                    ->whereDate('orders.created_at', $item->date)
                    ->select(DB::raw('SUM(order_items.quantity * products.cost_price) as total_cost'))
                    ->value('total_cost');

                // Example operating expenses (you should adjust this based on your actual expenses)
                $operatingExpenses = $item->revenue * 0.3; // 30% of revenue as operating expenses

                $grossProfit = $item->revenue - $costOfGoods;
                $netProfit = $grossProfit - $operatingExpenses;
                $profitMargin = $item->revenue > 0 ? ($netProfit / $item->revenue) * 100 : 0;

                return [
                    'date' => $item->date,
                    'revenue' => $item->revenue,
                    'costOfGoods' => $costOfGoods,
                    'grossProfit' => $grossProfit,
                    'operatingExpenses' => $operatingExpenses,
                    'netProfit' => $netProfit,
                    'profitMargin' => round($profitMargin, 2)
                ];
            });

        return response()->json([
            'profitAnalysis' => $profitAnalysis
        ]);
    }

    private function getPaymentMethodsForDate($date)
    {
        return Order::where('status', 'completed')
            ->whereDate('created_at', $date)
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();
    }

    private function exportToExcel($data, $filename)
    {
        // Implementation for Excel export
        // You'll need to install a package like PhpSpreadsheet
        // This is a placeholder for the actual implementation
        return response()->json(['error' => 'Excel export not implemented'], 501);
    }

    private function exportToPDF($data, $filename)
    {
        // Implementation for PDF export
        // You'll need to install a package like DomPDF
        // This is a placeholder for the actual implementation
        return response()->json(['error' => 'PDF export not implemented'], 501);
    }
} 