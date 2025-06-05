@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Notifications</h2>
                    @if($notifications->isNotEmpty())
                        <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="card-body">
                    @if($notifications->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No notifications found.</p>
                        </div>
                    @else
                        <div class="list-group">
                            @foreach($notifications as $notification)
                                <div class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'list-group-item-primary' }}">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">{{ $notification->data['title'] ?? 'Notification' }}</h5>
                                            <p class="mb-1">{{ $notification->data['message'] ?? '' }}</p>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            @if(!$notification->read_at)
                                                <form action="{{ route('notifications.markAsRead') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        Mark as Read
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 