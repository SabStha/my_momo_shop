@extends('layouts.admin')

@section('title', 'QR Code Generator')

@section('content')
<div class="max-w-7xl mx-auto py-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-white">📱 QR Code Generator</h3>
                <div class="flex space-x-4">
                    <a href="{{ route('admin.wallet.index') }}" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Wallets
                    </a>
                </div>
                                </div>
                        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Form Section -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <form id="qrForm" class="space-y-4">
                        @csrf
                        <div class="relative">
                            <label for="userSearch" class="block text-sm font-medium text-gray-700">Search User</label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="userSearch" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                       placeholder="Type to search users..."
                                       autocomplete="off">
                                <div id="searchResults" 
                                     class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-300 hidden"
                                     role="listbox"></div>
                            </div>
                        </div>

                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Selected User</label>
                            <input type="text" 
                                   id="selectedUserName" 
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm" 
                                   readonly>
                            <input type="hidden" 
                                   id="user_id" 
                                   name="user_id">
                        </div>

                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" 
                                       id="amount" 
                                       name="amount" 
                                       step="0.01" 
                                       min="0.01" 
                                       class="block w-full pl-7 pr-12 rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                                       placeholder="0.00">
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-qrcode mr-2"></i>
                            Generate QR Code
                        </button>
                    </form>
                    </div>

                <!-- QR Code Display Section -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <div id="qrDisplay" class="hidden">
                        <div class="text-center mb-4">
                            <h4 class="text-lg font-medium text-gray-900" id="qrUser"></h4>
                            <p class="text-sm text-gray-500" id="qrAmount"></p>
                        </div>
                        <div class="flex justify-center">
                            <div id="qrCode" class="bg-white p-4 rounded-lg shadow"></div>
                        </div>
                        <div class="mt-4 text-center">
                            <button onclick="printQRCode()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <i class="fas fa-print mr-2"></i>
                                Print QR Code
                        </button>
                        </div>
                    </div>
                    <div id="qrPlaceholder" class="text-center text-gray-500 py-12">
                        <i class="fas fa-qrcode text-4xl mb-4"></i>
                        <p>Select a user and amount to generate QR code</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Search functionality
const userSearch = document.getElementById('userSearch');
const searchResults = document.getElementById('searchResults');
const selectedUserName = document.getElementById('selectedUserName');
const userIdInput = document.getElementById('user_id');
let searchTimeout;

userSearch.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();

    if (query.length < 2) {
        searchResults.classList.add('hidden');
            return;
        }

    searchTimeout = setTimeout(() => {
        fetch(`/admin/users/search?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
                searchResults.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(user => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-2 hover:bg-blue-50 cursor-pointer';
                        div.textContent = `${user.name} (${user.email})`;
                        div.addEventListener('click', () => {
                            userIdInput.value = user.id;
                            selectedUserName.value = `${user.name} (${user.email})`;
                            userSearch.value = '';
                            searchResults.classList.add('hidden');
                        });
                        searchResults.appendChild(div);
                    });
                    searchResults.classList.remove('hidden');
            } else {
                    searchResults.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
                searchResults.classList.add('hidden');
        });
    }, 300);
    });

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    if (!userSearch.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.add('hidden');
    }
    });

// QR Code generation
document.getElementById('qrForm').addEventListener('submit', async function(e) {
        e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('{{ route("admin.wallet.generate-qr") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            document.getElementById('qrDisplay').classList.remove('hidden');
            document.getElementById('qrPlaceholder').classList.add('hidden');
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

function printQRCode() {
    const printWindow = window.open('', '_blank');
    const qrContent = document.getElementById('qrCode').innerHTML;
    const user = document.getElementById('qrUser').textContent;
    const amount = document.getElementById('qrAmount').textContent;
    
    printWindow.document.write(`
        <html>
            <head>
                <title>QR Code - ${user}</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif;
                        text-align: center;
                        padding: 20px;
                    }
                    .qr-container {
                        margin: 20px auto;
                        max-width: 300px;
                    }
                    .user-info {
                        margin: 20px 0;
                        font-size: 18px;
                    }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="user-info">
                    <h2>${user}</h2>
                    <p>${amount}</p>
                </div>
                <div class="qr-container">
                    ${qrContent}
                </div>
                <div class="no-print">
                    <button onclick="window.print()">Print</button>
                    </div>
            </body>
        </html>
    `);
    printWindow.document.close();
}
</script>
@endpush