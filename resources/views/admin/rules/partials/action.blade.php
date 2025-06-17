<div class="action-item border rounded-md p-4 mb-4">
    <div class="grid grid-cols-1 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Action Type</label>
            <select name="actions[{{ $index }}][type]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="updateActionFields({{ $index }})">
                <option value="launch_campaign" {{ $action['type'] === 'launch_campaign' ? 'selected' : '' }}>Launch Campaign</option>
                <option value="update_customer" {{ $action['type'] === 'update_customer' ? 'selected' : '' }}>Update Customer</option>
                <option value="send_notification" {{ $action['type'] === 'send_notification' ? 'selected' : '' }}>Send Notification</option>
            </select>
        </div>
        <div class="action-fields">
            @switch($action['type'])
                @case('launch_campaign')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Campaign</label>
                        <select name="actions[{{ $index }}][campaign_id]" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @foreach($campaigns as $campaign)
                                <option value="{{ $campaign->id }}" {{ $action['campaign_id'] == $campaign->id ? 'selected' : '' }}>
                                    {{ $campaign->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @break
                @case('update_customer')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Updates (JSON)</label>
                        <textarea name="actions[{{ $index }}][updates]" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder='{"is_vip": true, "risk_level": "low"}'>{{ json_encode($action['updates'] ?? [], JSON_PRETTY_PRINT) }}</textarea>
                    </div>
                    @break
                @case('send_notification')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notification Message</label>
                        <textarea name="actions[{{ $index }}][message]" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ $action['message'] ?? '' }}</textarea>
                    </div>
                    @break
            @endswitch
        </div>
        <div class="flex justify-end">
            <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()" class="text-red-600 hover:text-red-900">Remove</button>
        </div>
    </div>
</div> 