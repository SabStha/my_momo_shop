@extends('layouts.admin')

@section('content')
<div class="container mx-auto">
    <div class="flex justify-center">
        <div class="w-full">
            <div class="bg-white shadow-md rounded my-6">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-xl font-semibold">POS & Payment Manager Access Logs</h3>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="w-1/6 py-2 px-4">Date & Time</th>
                                    <th class="w-1/6 py-2 px-4">User</th>
                                    <th class="w-1/6 py-2 px-4">Access Type</th>
                                    <th class="w-1/6 py-2 px-4">Action</th>
                                    <th class="w-1/6 py-2 px-4">IP Address</th>
                                    <th class="w-1/6 py-2 px-4">Device</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                @foreach($posAccessLogs as $log)
                                <tr>
                                    <td class="border px-4 py-2">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                    <td class="border px-4 py-2">{{ $log->user->name }}</td>
                                    <td class="border px-4 py-2">
                                        @if($log->access_type === 'pos')
                                            <span class="px-2 py-1 bg-blue-500 text-white rounded">POS</span>
                                        @else
                                            <span class="px-2 py-1 bg-green-500 text-white rounded">Payment Manager</span>
                                        @endif
                                    </td>
                                    <td class="border px-4 py-2">
                                        @if($log->action === 'login')
                                            <span class="px-2 py-1 bg-green-500 text-white rounded">Login</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-500 text-white rounded">Logout</span>
                                        @endif
                                    </td>
                                    <td class="border px-4 py-2">{{ $log->ip_address }}</td>
                                    <td class="border px-4 py-2">{{ $log->user_agent }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $posAccessLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 