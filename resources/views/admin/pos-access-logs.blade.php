@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Access Logs</h2>

    <!-- POS Access Logs -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>POS Access Logs</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posAccessLogs as $log)
                        <tr>
                            <td>{{ $log->user->name }}</td>
                            <td>{{ ucfirst($log->action) }}</td>
                            <td>
                                @if($log->details && isset($log->details['status']) && $log->details['status'] === 'success')
                                    <span class="badge bg-success">Success</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                    @if($log->details && isset($log->details['reason']))
                                        <small class="text-muted">({{ $log->details['reason'] }})</small>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Manager Access Logs -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Payment Manager Access Logs</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentManagerLogs as $log)
                        <tr>
                            <td>{{ $log->user->name }}</td>
                            <td>{{ ucfirst($log->action) }}</td>
                            <td>
                                @if($log->details && isset($log->details['status']) && $log->details['status'] === 'success')
                                    <span class="badge bg-success">Success</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                    @if($log->details && isset($log->details['reason']))
                                        <small class="text-muted">({{ $log->details['reason'] }})</small>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- POS Order Logs -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>POS Order Logs</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Items</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posOrderLogs as $log)
                        <tr>
                            <td>{{ $log->user->name }}</td>
                            <td>{{ $log->details['order_id'] ?? 'N/A' }}</td>
                            <td>₱{{ number_format($log->details['total_amount'] ?? 0, 2) }}</td>
                            <td>{{ $log->details['items_count'] ?? 0 }}</td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Receiver Logs -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Payment Receiver Logs</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentLogs as $log)
                        <tr>
                            <td>{{ $log->user->name }}</td>
                            <td>{{ $log->details['order_id'] ?? 'N/A' }}</td>
                            <td>₱{{ number_format($log->details['amount'] ?? 0, 2) }}</td>
                            <td>{{ $log->details['payment_method'] ?? 'N/A' }}</td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 