<div class="container py-3">
    <h2>2-Day Inventory Forecast</h2>
    @if(isset($forecast) && count($forecast) > 0)
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Current Stock</th>
                    <th>Avg Daily Usage</th>
                    <th>Needed (2 days)</th>
                    <th>Suggested Order</th>
                    <th>Unit</th>
                    <th>Trend</th>
                    <th>Safety Stock</th>
                    <th>Reorder Point</th>
                    <th>Status</th>
                    <th>Last Count</th>
                    <th>Count Frequency</th>
                </tr>
            </thead>
            <tbody>
                @foreach($forecast as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ number_format($item['current'], 2) }}</td>
                    <td>{{ number_format($item['avg'], 2) }}</td>
                    <td>{{ number_format($item['needed'], 2) }}</td>
                    <td>{{ number_format($item['suggested'], 2) }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td>{{ number_format($item['trend'], 2) }}%</td>
                    <td>{{ number_format($item['safety_stock'], 2) }}</td>
                    <td>{{ number_format($item['reorder_point'], 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $item['status'] === 'OK' ? 'success' : 'danger' }}">
                            {{ $item['status'] }}
                        </span>
                    </td>
                    <td>{{ $item['last_count'] }}</td>
                    <td>{{ $item['count_frequency'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-info">
        No forecast data available.
    </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Edit button click
    $('.edit-btn').click(function() {
        const rowId = $(this).data('id');
        const row = $(`#row-${rowId}`);
        
        // Show edit mode
        row.find('.view-mode').hide();
        row.find('.edit-mode').show();
        row.find('.edit-btn').hide();
        row.find('.save-btn, .cancel-btn').show();
    });

    // Cancel button click
    $('.cancel-btn').click(function() {
        const rowId = $(this).data('id');
        const row = $(`#row-${rowId}`);
        
        // Show view mode
        row.find('.view-mode').show();
        row.find('.edit-mode').hide();
        row.find('.edit-btn').show();
        row.find('.save-btn, .cancel-btn').hide();
    });

    // Save button click
    $('.save-btn').click(function() {
        const rowId = $(this).data('id');
        const row = $(`#row-${rowId}`);
        const data = {
            id: rowId,
            current_quantity: row.find('[name="current_quantity"]').val(),
            avg_usage: row.find('[name="avg_usage"]').val(),
            trend: row.find('[name="trend"]').val(),
            safety_stock: row.find('[name="safety_stock"]').val(),
            reorder_point: row.find('[name="reorder_point"]').val(),
            suggested: row.find('[name="suggested"]').val(),
            status: row.find('[name="status"]').val()
        };

        // Send AJAX request to update
        $.ajax({
            url: '/admin/inventory/forecast/update',
            method: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update view mode values
                    row.find('.view-mode').each(function() {
                        const input = row.find(`[name="${$(this).data('field')}"]`);
                        if (input.length) {
                            $(this).text(input.val());
                        }
                    });
                    
                    // Show view mode
                    row.find('.view-mode').show();
                    row.find('.edit-mode').hide();
                    row.find('.edit-btn').show();
                    row.find('.save-btn, .cancel-btn').hide();

                    // Show success message
                    alert('Forecast updated successfully');
                } else {
                    alert('Error updating forecast');
                }
            },
            error: function() {
                alert('Error updating forecast');
            }
        });
    });
});
</script>
@endpush 