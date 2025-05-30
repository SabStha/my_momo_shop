@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Creator Insights</h1>
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="rank" class="form-label">Rank</label>
            <select name="rank" id="rank" class="form-select">
                <option value="">All</option>
                @foreach($ranks as $rank)
                    <option value="{{ $rank }}" @if(request('rank') == $rank) selected @endif>{{ $rank }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label">Referral Status</label>
            <select name="status" id="status" class="form-select">
                <option value="">All</option>
                <option value="pending" @if(request('status') == 'pending') selected @endif>Pending</option>
                <option value="signed_up" @if(request('status') == 'signed_up') selected @endif>Signed Up</option>
                <option value="ordered" @if(request('status') == 'ordered') selected @endif>Ordered</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="date_from" class="form-label">From</label>
            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-3">
            <label for="date_to" class="form-label">To</label>
            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Referral Code</th>
                    <th>Total Points</th>
                    <th># Referrals</th>
                    <th># Orders</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creators as $creator)
                <tr>
                    <td>{{ $creator->user->name }}</td>
                    <td><span class="badge bg-primary">{{ $creator->code }}</span></td>
                    <td>{{ $creator->points }}</td>
                    <td>{{ $creator->referrals_count }}</td>
                    <td>{{ $creator->orders_count }}</td>
                    <td>
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#referralTreeModal{{ $creator->id }}">View Referral Tree</button>
                    </td>
                </tr>
                <!-- Referral Tree Modal -->
                <div class="modal fade" id="referralTreeModal{{ $creator->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Referral Tree for {{ $creator->user->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-group">
                                    @foreach($creator->referrals as $ref)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>{{ $ref->referredUser->name ?? $ref->referredUser->email ?? 'N/A' }}</span>
                                            <span class="badge bg-secondary">{{ ucfirst($ref->status) }}</span>
                                            <span class="text-muted small">{{ $ref->created_at ? $ref->created_at->format('Y-m-d') : '' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 