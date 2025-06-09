@extends('layouts.admin')

@section('title', 'Daily Stock Check')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-2xl font-semibold text-gray-800">Daily Stock Check</h2>
        <span class="text-sm text-gray-500 mt-2 sm:mt-0">📅 {{ now()->format('F j, Y') }}</span>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.inventory.checks.store') }}" method="POST" id="stockCheckForm">
        @csrf
        <input type="hidden" name="branch_id" value="{{ $branchId }}">

        <div class="overflow-x-auto bg-white shadow-md rounded-lg">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-4 bg-blue-200 text-gray-900 font-semibold">SN</th>
                        <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold"">Item Name</th>
                        <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">SKU</th>
                        <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">Current Stock</th>
                        <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">Checked Quantity</th>
                        <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">Notes</th>
                        <th class="px-6 py-4 bg-blue-400 text-gray-900 font-semibold">Last Checked</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 font-bold text-gray-800 bg-blue-100">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 py-4 text-gray-800">{{ $item->sku }}</td>
                            <td class="px-6 py-4 text-gray-800">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="px-6 py-4">
                                <input 
                                    type="number"
                                    name="quantities[{{ $item->id }}]"
                                    value="{{ $item->dailyChecks->first()?->quantity_checked ?? $item->current_stock }}"
                                    step="0.01"
                                    min="0"
                                    required
                                    class="w-28 px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                            </td>
                            <td class="px-6 py-4">
                                <input 
                                    type="text" 
                                    name="notes[{{ $item->id }}]" 
                                    value="{{ $item->dailyChecks->first()?->notes }}"
                                    placeholder="Add notes..."
                                    class="w-full px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                @if($item->dailyChecks->first())
                                    <span class="block text-sm font-medium text-green-700">
                                        {{ $item->dailyChecks->first()->created_at->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic">Not checked</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-save"></i> Save All Checks
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('stockCheckForm').addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to save all stock checks?')) {
            e.preventDefault();
        }
    });
</script>
@endpush
@endsection
