@extends('layouts.admin')

@section('title', ($branch ? $branch->name : 'Universal') . ' Stock Check')

@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">
    <div class="mb-6 bg-white shadow rounded-lg p-4 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-clipboard-check text-blue-600 mr-2"></i>
                Stock Check - {{ $branch ? $branch->name : 'Universal (All Branches/Main)' }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                View daily, weekly, and monthly stock health and recommendations.
            </p>
        </div>
        <a href="{{ route('admin.inventory.index', $branch ? ['branch' => $branch->id] : []) }}"
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Back to Inventory
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-blue-50 border border-blue-200 rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-blue-800 mb-2"><i class="fas fa-calendar-day mr-1"></i> Daily Check</h3>
            <ul class="text-sm text-gray-700 mb-2">
                <li><b>Critical Alerts:</b> {{ $daily['summary']['critical_alerts_count'] ?? 0 }}</li>
                <li><b>Out of Stock:</b> {{ $daily['summary']['out_of_stock_count'] ?? 0 }}</li>
                <li><b>Low Stock:</b> {{ $daily['summary']['low_stock_count'] ?? 0 }}</li>
                <li><b>Status:</b> <span class="font-bold uppercase">{{ $daily['summary']['overall_status'] ?? 'N/A' }}</span></li>
            </ul>
            <details class="mb-2">
                <summary class="cursor-pointer text-blue-600">View Details</summary>
                <div class="mt-2">
                    @if(!empty($daily['critical_alerts']))
                        <div class="mb-2 text-red-700 font-semibold">Critical Alerts:</div>
                        <ul class="mb-2">
                            @foreach($daily['critical_alerts'] as $alert)
                                <li>• {{ $alert['message'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($daily['out_of_stock_items']))
                        <div class="mb-2 text-red-600 font-semibold">Out of Stock Items:</div>
                        <ul class="mb-2">
                            @foreach($daily['out_of_stock_items'] as $item)
                                <li>• {{ $item['name'] }} ({{ $item['category'] }})</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($daily['low_stock_items']))
                        <div class="mb-2 text-yellow-700 font-semibold">Low Stock Items:</div>
                        <ul class="mb-2">
                            @foreach($daily['low_stock_items'] as $item)
                                <li>• {{ $item['name'] }} ({{ $item['current_stock'] }} units, {{ $item['stock_level_percentage'] }}% of reorder)</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($daily['recommendations']))
                        <div class="mb-2 text-green-700 font-semibold">Recommendations:</div>
                        <ul>
                            @foreach($daily['recommendations'] as $rec)
                                <li>• {{ $rec['message'] }}
                                    <ul class="ml-4 list-disc">
                                        @foreach($rec['actions'] as $action)
                                            <li>{{ $action }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </details>
            <div class="mt-4">
                <a href="{{ route('admin.inventory.checks.index', $branch ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                    <i class="fas fa-edit mr-1"></i> View/Edit All Items
                </a>
            </div>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-yellow-800 mb-2"><i class="fas fa-calendar-week mr-1"></i> Weekly Check</h3>
            <ul class="text-sm text-gray-700 mb-2">
                <li><b>Trends Analyzed:</b> {{ $weekly['summary']['trends_analyzed'] ?? 0 }}</li>
                <li><b>Suppliers Evaluated:</b> {{ $weekly['summary']['suppliers_evaluated'] ?? 0 }}</li>
                <li><b>Total Cost:</b> Rs. {{ number_format($weekly['summary']['total_weekly_cost'] ?? 0, 2) }}</li>
                <li><b>Status:</b> <span class="font-bold uppercase">{{ $weekly['summary']['overall_status'] ?? 'N/A' }}</span></li>
            </ul>
            <details class="mb-2">
                <summary class="cursor-pointer text-yellow-700">View Details</summary>
                <div class="mt-2">
                    @if(!empty($weekly['trend_analysis']))
                        <div class="mb-2 text-blue-700 font-semibold">Trends:</div>
                        <ul class="mb-2">
                            @foreach($weekly['trend_analysis'] as $trend)
                                <li>• {{ $trend['item_name'] }}: Used {{ $trend['weekly_usage'] }} units, Purchased {{ $trend['weekly_purchases'] }} units</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($weekly['supplier_performance']))
                        <div class="mb-2 text-purple-700 font-semibold">Supplier Performance:</div>
                        <ul class="mb-2">
                            @foreach($weekly['supplier_performance'] as $supplier)
                                <li>• {{ $supplier['supplier_name'] }}: {{ round($supplier['fulfillment_rate'], 1) }}% fulfillment ({{ $supplier['total_orders'] }} orders)</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($weekly['cost_analysis']))
                        <div class="mb-2 text-green-700 font-semibold">Cost Analysis:</div>
                        <ul>
                            <li>Total Purchases: Rs. {{ number_format($weekly['cost_analysis']['total_purchases'] ?? 0, 2) }}</li>
                            <li>Total Sales: Rs. {{ number_format($weekly['cost_analysis']['total_sales'] ?? 0, 2) }}</li>
                            <li>Total Waste: Rs. {{ number_format($weekly['cost_analysis']['total_waste'] ?? 0, 2) }}</li>
                            <li>Waste %: {{ round($weekly['cost_analysis']['waste_percentage'] ?? 0, 1) }}%</li>
                        </ul>
                    @endif
                    @if(!empty($weekly['recommendations']))
                        <div class="mb-2 text-green-700 font-semibold">Recommendations:</div>
                        <ul>
                            @foreach($weekly['recommendations'] as $rec)
                                <li>• {{ $rec['message'] }}
                                    <ul class="ml-4 list-disc">
                                        @foreach($rec['actions'] as $action)
                                            <li>{{ $action }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </details>
            <div class="mt-4">
                <a href="{{ route('admin.inventory.weekly-checks.index', $branch ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-3 py-2 bg-yellow-600 text-white text-sm rounded-md hover:bg-yellow-700">
                    <i class="fas fa-edit mr-1"></i> View/Edit All Items
                </a>
            </div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-green-800 mb-2"><i class="fas fa-calendar-alt mr-1"></i> Monthly Check</h3>
            <ul class="text-sm text-gray-700 mb-2">
                <li><b>Strategic Insights:</b> {{ $monthly['summary']['strategic_insights'] ?? 0 }}</li>
                <li><b>Profitability Items:</b> {{ $monthly['summary']['profitability_items'] ?? 0 }}</li>
                <li><b>Optimization:</b> {{ $monthly['summary']['optimization_opportunities'] ?? 0 }}</li>
                <li><b>Status:</b> <span class="font-bold uppercase">{{ $monthly['summary']['overall_status'] ?? 'N/A' }}</span></li>
            </ul>
            <details class="mb-2">
                <summary class="cursor-pointer text-green-700">View Details</summary>
                <div class="mt-2">
                    @if(!empty($monthly['strategic_analysis']))
                        <div class="mb-2 text-blue-700 font-semibold">Strategic Analysis:</div>
                        <ul>
                            <li>Monthly Sales: Rs. {{ number_format($monthly['strategic_analysis']['monthly_sales'] ?? 0, 2) }}</li>
                            <li>Monthly Purchases: Rs. {{ number_format($monthly['strategic_analysis']['monthly_purchases'] ?? 0, 2) }}</li>
                            <li>Monthly Waste: Rs. {{ number_format($monthly['strategic_analysis']['monthly_waste'] ?? 0, 2) }}</li>
                            <li>Gross Margin: Rs. {{ number_format($monthly['strategic_analysis']['gross_margin'] ?? 0, 2) }}</li>
                            <li>Waste %: {{ round($monthly['strategic_analysis']['waste_percentage'] ?? 0, 1) }}%</li>
                        </ul>
                    @endif
                    @if(!empty($monthly['profitability_analysis']))
                        <div class="mb-2 text-yellow-700 font-semibold">Top Profitable Items:</div>
                        <ul>
                            @foreach(array_slice($monthly['profitability_analysis'], 0, 5) as $item)
                                <li>• {{ $item['item_name'] }}: Rs. {{ number_format($item['profitability'], 2) }} profit ({{ round($item['profit_margin'], 1) }}% margin)</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($monthly['optimization_opportunities']))
                        <div class="mb-2 text-purple-700 font-semibold">Optimization Opportunities:</div>
                        <ul>
                            @foreach($monthly['optimization_opportunities'] as $opp)
                                <li>• {{ $opp['item_name'] }}: {{ $opp['type'] }} - {{ $opp['recommendation'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($monthly['recommendations']))
                        <div class="mb-2 text-green-700 font-semibold">Recommendations:</div>
                        <ul>
                            @foreach($monthly['recommendations'] as $rec)
                                <li>• {{ $rec['message'] }}
                                    <ul class="ml-4 list-disc">
                                        @foreach($rec['actions'] as $action)
                                            <li>{{ $action }}</li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </details>
            <div class="mt-4">
                <a href="{{ route('admin.inventory.monthly-checks.index', $branch ? ['branch' => $branch->id] : []) }}" 
                   class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                    <i class="fas fa-edit mr-1"></i> View/Edit All Items
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 
