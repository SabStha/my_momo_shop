@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Badges for {{ $user->name }} ({{ $user->email }})</h2>
<a href="{{ route('admin.badges.index') }}" class="btn btn-secondary mb-3">Back to All Users</a>

@if($user->userBadges->isEmpty())
    <div class="alert alert-info">This user has not earned any badges yet.</div>
@else
    <div class="row">
        @foreach($user->userBadges as $badge)
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <span style="font-size:1.5em;">{{ $badge->badgeClass->icon }}</span>
                            {{ $badge->badgeClass->name }}
                        </h5>
                        <p class="mb-1"><strong>Rank:</strong> {{ $badge->badgeRank->name }}</p>
                        <p class="mb-1"><strong>Tier:</strong> {{ $badge->badgeTier->name }}</p>
                        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-{{ $badge->status_color }}">{{ ucfirst($badge->status) }}</span></p>
                        <p class="mb-1"><strong>Earned:</strong> {{ $badge->earned_at->format('M d, Y') }}</p>
                        <p class="mb-1"><strong>How:</strong> {{ $badge->earned_data_text }}</p>
                        @if($badge->expires_at)
                            <p class="mb-1"><strong>Expires:</strong> {{ $badge->expires_at->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection 