@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Wallet Top-Up</h2>
                <a href="{{ route('admin.wallet.topup.logout') }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                    Logout Wallet Access
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Direct Top-Up Form -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Direct Top-Up</h3>
                    <form id="topUpForm" class="space-y-4">
                        @csrf
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Select User</label>
                            <select name="user_id" id="user_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select a user</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} (Current Balance: Rs {{ number_format($user->wallet?->balance ?? 0, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                            <input type="text" name="description" id="description"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Process Top-Up
                        </button>
                    </form>
                </div>

                <!-- QR Code Top-Up -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">QR Code Top-Up</h3>
                    <form id="qrForm" class="space-y-4">
                        @csrf
                        <div>
                            <label for="qr_user_id" class="block text-sm font-medium text-gray-700">Select User</label>
                            <select name="user_id" id="qr_user_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Select a user</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="qr_amount" class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" name="amount" id="qr_amount" step="0.01" min="0.01" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Generate QR Code
                        </button>
                    </form>

                    <div id="qrDisplay" class="mt-4 hidden">
                        <div class="bg-white p-4 rounded-lg shadow text-center">
                            <div id="qrCode" class="mb-4"></div>
                            <p class="text-sm text-gray-600">Scan this QR code to top up</p>
                            <p class="font-semibold" id="qrUser"></p>
                            <p class="text-lg font-bold text-indigo-600" id="qrAmount"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Direct Top-Up Form
    document.getElementById('topUpForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("admin.wallet.topup.process") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert('Top-up successful! New balance: $' + data.new_balance);
                location.reload();
            } else {
                alert(data.error || 'Error processing top-up');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error processing top-up');
        }
    });

    // QR Code generation
    document.getElementById('qrForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("admin.wallet.topup.generate-qr") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                document.getElementById('qrDisplay').classList.remove('hidden');
                document.getElementById('qrUser').textContent = data.user;
                document.getElementById('qrAmount').textContent = `$${parseFloat(data.amount).toFixed(2)}`;
                
                // Create and display QR code image
                const qrCodeDiv = document.getElementById('qrCode');
                qrCodeDiv.innerHTML = ''; // Clear previous content
                const img = document.createElement('img');
                img.src = data.qr_code;
                img.alt = 'QR Code';
                img.className = 'w-64 h-64 mx-auto';
                qrCodeDiv.appendChild(img);
            } else {
                alert('Error generating QR code');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error generating QR code');
        }
    });
</script>
@endpush
@endsection 