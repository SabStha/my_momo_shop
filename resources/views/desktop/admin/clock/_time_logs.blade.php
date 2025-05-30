@forelse($timeLogs as $log)
    <tr>
        <td>{{ $log->employee->user->name }}</td>
        <td>{{ $log->clock_in->format('H:i:s') }}</td>
        <td>{{ $log->clock_out ? $log->clock_out->format('H:i:s') : '-' }}</td>
        <td>{{ $log->break_start ? $log->break_start->format('H:i:s') : '-' }}</td>
        <td>{{ $log->break_end ? $log->break_end->format('H:i:s') : '-' }}</td>
        <td>
            @if($log->status === 'completed')
                <span class="badge bg-secondary">Completed</span>
            @elseif($log->status === 'on_break')
                <span class="badge bg-warning">On Break</span>
            @else
                <span class="badge bg-success">Active</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center">No clock records for today.</td>
    </tr>
@endforelse 