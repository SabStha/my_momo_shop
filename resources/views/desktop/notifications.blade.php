@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 700px; margin: 2rem auto;">
    <h1 style="font-weight: 700; margin-bottom: 1.5rem;">Notifications</h1>
    
    <div class="notifications-list" style="display: flex; flex-direction: column; gap: 1rem;">
        @foreach($notifications as $notification)
            <div class="notification-item" style="background: #fff; border-radius: 12px; padding: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06); {{ !$notification['read'] ? 'border-left: 4px solid #c1440e;' : '' }}">
                <div class="notification-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: #333;">{{ $notification['title'] }}</h3>
                    <span style="color: #888; font-size: 0.9rem;">{{ $notification['time'] }}</span>
                </div>
                <p style="margin: 0; color: #666;">{{ $notification['message'] }}</p>
                @if(!$notification['read'])
                    <div style="margin-top: 0.5rem;">
                        <span style="background: #fff0f0; color: #c1440e; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">New</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection 