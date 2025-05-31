@extends('desktop.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">POS & Payment Manager Access Logs</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Access Type</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                    <th>Device</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                    <td>{{ $log->user->name }}</td>
                                    <td>
                                        @if($log->access_type === 'pos')
                                            <span class="badge bg-primary">POS</span>
                                        @else
                                            <span class="badge bg-success">Payment Manager</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->action === 'login')
                                            <span class="badge bg-success">Login</span>
                                        @else
                                            <span class="badge bg-danger">Logout</span>
                                        @endif
                                    </td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>{{ $log->user_agent }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 