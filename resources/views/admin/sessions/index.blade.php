@extends('layouts.admin')

@section('title', 'Session Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Session Management</h2>
        <div class="flex space-x-4">
            @if(!$activeSession)
                <button onclick="openSessionModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    Open New Session
                </button>
            @else
                <button onclick="closeSessionModal()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Close Current Session
                </button>
            @endif
        </div>
    </div>

    {{-- Active Session Card --}}
    @if($activeSession)
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 border-l-4 border-green-500">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Active Session</h3>
                <p class="text-sm text-gray-600">Opened by {{ $activeSession->openedBy->name }} at {{ $activeSession->opened_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Opening Cash</p>
                <p class="text-lg font-semibold text-gray-800">{{ number_format($activeSession->opening_cash, 2) }}</p>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-600">Total Sales</p>
                <p class="text-lg font-semibold text-gray-800">{{ number_format($activeSession->total_sales, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Orders</p>
                <p class="text-lg font-semibold text-gray-800">{{ $activeSession->total_orders }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Voided Orders</p>
                <p class="text-lg font-semibold text-gray-800">{{ $activeSession->voided_orders }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Duration</p>
                <p class="text-lg font-semibold text-gray-800">{{ $activeSession->opened_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Session History --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Session History</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opened By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opening Cash</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($sessions as $session)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">#{{ $session->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $session->openedBy->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($session->opening_cash, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($session->total_sales, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $session->status === 'active' ? 'bg-green-100 text-green-800' : 
                               ($session->status === 'closed' ? 'bg-gray-100 text-gray-800' : 
                               'bg-red-100 text-red-800') }}">
                            {{ ucfirst($session->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($session->closed_at)
                            {{ $session->opened_at->diffInHours($session->closed_at) }} hours
                        @else
                            {{ $session->opened_at->diffForHumans() }}
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.sessions.show', $session) }}" class="text-indigo-600 hover:text-indigo-900">View Details</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No sessions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $sessions->links() }}
        </div>
    </div>
</div>

@include('admin.sessions.partials.modals')

@endsection

@push('scripts')
<script>
    function openSessionModal() {
        document.getElementById('openSessionModal').classList.remove('hidden');
    }

    function closeOpenSessionModal() {
        document.getElementById('openSessionModal').classList.add('hidden');
    }

    function closeSessionModal() {
        document.getElementById('closeSessionModal').classList.remove('hidden');
    }

    function closeCloseSessionModal() {
        document.getElementById('closeSessionModal').classList.add('hidden');
    }

    async function handleOpenSession(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route("admin.sessions.open") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while opening the session.');
        }
    }

    async function handleCloseSession(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route("admin.sessions.close", $activeSession) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while closing the session.');
        }
    }
</script>
@endpush 