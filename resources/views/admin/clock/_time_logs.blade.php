@forelse($timeLogs as $log)
    <tr>
        <td class="px-4 py-2 border">{{ $log->employee->user->name }}</td>
        <td class="px-4 py-2 border">{{ $log->clock_in->format('H:i:s') }}</td>
        <td class="px-4 py-2 border">{{ $log->clock_out ? $log->clock_out->format('H:i:s') : '-' }}</td>
        <td class="px-4 py-2 border">{{ $log->break_start ? $log->break_start->format('H:i:s') : '-' }}</td>
        <td class="px-4 py-2 border">{{ $log->break_end ? $log->break_end->format('H:i:s') : '-' }}</td>
        <td class="px-4 py-2 border">
            @if($log->status === 'completed')
                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-800">Completed</span>
            @elseif($log->status === 'on_break')
                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-yellow-200 text-yellow-800">On Break</span>
            @else
                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-200 text-green-800">Active</span>
            @endif
        </td>
        <td class="px-4 py-2 border">
            <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded edit-log" 
                    data-bs-toggle="modal" 
                    data-bs-target="#editModal"
                    data-log-id="{{ $log->id }}"
                    data-clock-in="{{ $log->clock_in ? $log->clock_in->format('Y-m-d\TH:i:s') : '' }}"
                    data-clock-out="{{ $log->clock_out ? $log->clock_out->format('Y-m-d\TH:i:s') : '' }}"
                    data-break-start="{{ $log->break_start ? $log->break_start->format('Y-m-d\TH:i:s') : '' }}"
                    data-break-end="{{ $log->break_end ? $log->break_end->format('Y-m-d\TH:i:s') : '' }}"
                    data-notes="{{ $log->notes }}">
                Edit
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-4 py-2 border text-center">No clock records for today.</td>
    </tr>
@endforelse 