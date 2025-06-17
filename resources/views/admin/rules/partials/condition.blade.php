<div class="condition-item border rounded-md p-4 mb-4">
    <div class="grid grid-cols-1 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Condition Type</label>
            <select name="conditions[{{ $index }}][type]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="updateConditionFields({{ $index }})">
                <option value="risk_level" {{ $condition['type'] === 'risk_level' ? 'selected' : '' }}>Risk Level</option>
                <option value="vip_status" {{ $condition['type'] === 'vip_status' ? 'selected' : '' }}>VIP Status</option>
                <option value="purchase_frequency" {{ $condition['type'] === 'purchase_frequency' ? 'selected' : '' }}>Purchase Frequency</option>
                <option value="spending_amount" {{ $condition['type'] === 'spending_amount' ? 'selected' : '' }}>Spending Amount</option>
                <option value="last_purchase" {{ $condition['type'] === 'last_purchase' ? 'selected' : '' }}>Last Purchase</option>
            </select>
        </div>
        <div class="condition-fields">
            @switch($condition['type'])
                @case('risk_level')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Risk Level</label>
                        <select name="conditions[{{ $index }}][value]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="high" {{ $condition['value'] === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ $condition['value'] === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ $condition['value'] === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    @break
                @case('vip_status')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">VIP Status</label>
                        <select name="conditions[{{ $index }}][value]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="true" {{ $condition['value'] === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ $condition['value'] === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    @break
                @case('purchase_frequency')
                @case('spending_amount')
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Operator</label>
                            <select name="conditions[{{ $index }}][operator]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="equals" {{ $condition['operator'] === 'equals' ? 'selected' : '' }}>Equals</option>
                                <option value="not_equals" {{ $condition['operator'] === 'not_equals' ? 'selected' : '' }}>Not Equals</option>
                                <option value="greater_than" {{ $condition['operator'] === 'greater_than' ? 'selected' : '' }}>Greater Than</option>
                                <option value="less_than" {{ $condition['operator'] === 'less_than' ? 'selected' : '' }}>Less Than</option>
                                <option value="greater_than_or_equal" {{ $condition['operator'] === 'greater_than_or_equal' ? 'selected' : '' }}>Greater Than or Equal</option>
                                <option value="less_than_or_equal" {{ $condition['operator'] === 'less_than_or_equal' ? 'selected' : '' }}>Less Than or Equal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Value</label>
                            <input type="number" name="conditions[{{ $index }}][value]" value="{{ $condition['value'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Period (days)</label>
                            <input type="number" name="conditions[{{ $index }}][period]" value="{{ $condition['period'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    @break
                @case('last_purchase')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Operator</label>
                            <select name="conditions[{{ $index }}][operator]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="equals" {{ $condition['operator'] === 'equals' ? 'selected' : '' }}>Equals</option>
                                <option value="not_equals" {{ $condition['operator'] === 'not_equals' ? 'selected' : '' }}>Not Equals</option>
                                <option value="greater_than" {{ $condition['operator'] === 'greater_than' ? 'selected' : '' }}>Greater Than</option>
                                <option value="less_than" {{ $condition['operator'] === 'less_than' ? 'selected' : '' }}>Less Than</option>
                                <option value="greater_than_or_equal" {{ $condition['operator'] === 'greater_than_or_equal' ? 'selected' : '' }}>Greater Than or Equal</option>
                                <option value="less_than_or_equal" {{ $condition['operator'] === 'less_than_or_equal' ? 'selected' : '' }}>Less Than or Equal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Days Ago</label>
                            <input type="number" name="conditions[{{ $index }}][value]" value="{{ $condition['value'] }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    @break
            @endswitch
        </div>
        <div class="flex justify-end">
            <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()" class="text-red-600 hover:text-red-900">Remove</button>
        </div>
    </div>
</div> 