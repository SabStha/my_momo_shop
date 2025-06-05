<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        @if($transaction->type === 'credit')
                            <span class="badge bg-success">Credit</span>
                        @else
                            <span class="badge bg-danger">Debit</span>
                        @endif
                    </td>
                    <td>₱{{ number_format($transaction->amount, 2) }}</td>
                    <td>{{ $transaction->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div> 