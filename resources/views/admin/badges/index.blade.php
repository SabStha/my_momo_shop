@extends('layouts.admin')

@section('content')
<h2 class="mb-4">All Users & Badges</h2>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>User</th>
            <th>Email</th>
            <th>Badges</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @foreach($user->userBadges as $badge)
                        <span title="{{ $badge->badgeClass->name }} - {{ $badge->badgeRank->name }} {{ $badge->badgeTier->name }}">
                            {{ $badge->badgeClass->icon }}
                        </span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('admin.badges.show', $user->id) }}" class="btn btn-sm btn-primary">View Details</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection 