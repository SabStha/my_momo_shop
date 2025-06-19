{{-- Open Session Modal --}}
<div id="openSessionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-4xl w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Open New Session</h3>
            <button onclick="closeOpenSessionModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="openSessionForm" method="POST" action="{{ route('admin.sessions.store') }}">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $branch->id ?? session('selected_branch_id') }}">
            <div class="space-y-4">
                <div>
                    <label for="opening_cash" class="block text-sm font-medium text-gray-700">Opening Cash Amount</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">Rs</span>
                        </div>
                        <input type="number" 
                               name="opening_cash" 
                               id="opening_cash" 
                               class="form-control"
                               value="{{ isset($denominations) ? array_sum(array_map(function($denomination) { return $denomination['value'] * ($denomination['default'] ?? 0); }, $denominations)) : 0 }}"
                               required>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Cash Denominations</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @php
                            $denominations = [
                                ['value' => 1000, 'name' => 'Rs 1,000', 'default' => 0],
                                ['value' => 500, 'name' => 'Rs 500', 'default' => 5],
                                ['value' => 100, 'name' => 'Rs 100', 'default' => 30],
                                ['value' => 50, 'name' => 'Rs 50', 'default' => 30],
                                ['value' => 20, 'name' => 'Rs 20', 'default' => 30],
                                ['value' => 10, 'name' => 'Rs 10', 'default' => 30],
                                ['value' => 5, 'name' => 'Rs 5', 'default' => 30],
                                ['value' => 1, 'name' => 'Rs 1', 'default' => 20]
                            ];
                        @endphp

                        @foreach($denominations as $denomination)
                        <div class="border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $denomination['name'] }}</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" 
                                       name="denominations[{{ $denomination['value'] }}]" 
                                       class="denomination-input focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                       min="0"
                                       value="{{ $denomination['default'] }}"
                                       data-value="{{ $denomination['value'] }}"
                                       onchange="updateTotalCash()">
                                <span class="text-sm text-gray-500">× Rs {{ number_format($denomination['value']) }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 denomination-total">Total: Rs {{ $denomination['value'] * ($denomination['default'] ?? 0) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" 
                              id="notes" 
                              rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeOpenSessionModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Open Session
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Close Session Modal --}}
<div id="closeSessionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-4xl w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Close Session</h3>
            <button onclick="closeCloseSessionModal()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="closeSessionForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="closing_cash" class="block text-sm font-medium text-gray-700">Closing Cash Amount</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">Rs</span>
                        </div>
                        <input type="number" 
                               name="closing_cash" 
                               id="closing_cash" 
                               readonly
                               class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-12 pr-12 sm:text-sm border-gray-300 rounded-md" 
                               placeholder="0.00">
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Cash Denominations</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @php
                            $denominations = [
                                ['value' => 1000, 'name' => 'Rs 1,000'],
                                ['value' => 500, 'name' => 'Rs 500'],
                                ['value' => 100, 'name' => 'Rs 100'],
                                ['value' => 50, 'name' => 'Rs 50'],
                                ['value' => 20, 'name' => 'Rs 20'],
                                ['value' => 10, 'name' => 'Rs 10'],
                                ['value' => 5, 'name' => 'Rs 5'],
                                ['value' => 1, 'name' => 'Rs 1']
                            ];
                        @endphp

                        @foreach($denominations as $denomination)
                        <div class="border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $denomination['name'] }}</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" 
                                       name="denominations[{{ $denomination['value'] }}]" 
                                       class="denomination-input focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                       min="0"
                                       value="0"
                                       data-value="{{ $denomination['value'] }}"
                                       onchange="updateTotalCash()">
                                <span class="text-sm text-gray-500">× Rs {{ number_format($denomination['value']) }}</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 denomination-total">Total: Rs {{ $denomination['value'] * ($denomination['default'] ?? 0) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="close_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" 
                              id="close_notes" 
                              rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeCloseSessionModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Close Session
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openOpenSessionModal() {
        document.getElementById('openSessionModal').classList.remove('hidden');
        // Load current denomination counts
        loadCurrentDenominations();
    }

    function closeOpenSessionModal() {
        document.getElementById('openSessionModal').classList.add('hidden');
    }

    function loadCurrentDenominations() {
        // Fetch current denomination counts from the server
        fetch('{{ route("admin.cash-denominations.total") }}')
            .then(response => response.json())
            .then(data => {
                // Update denomination inputs with current counts
                Object.entries(data.denominations).forEach(([value, count]) => {
                    const input = document.querySelector(`input[name="denominations[${value}]"]`);
                    if (input) {
                        input.value = count;
                        updateDenominationTotal(input);
                    }
                });
                updateTotalCash();
            });
    }

    function updateDenominationTotal(input) {
        const value = parseInt(input.dataset.value);
        const count = parseInt(input.value) || 0;
        const total = value * count;
        const totalElement = input.closest('.border').querySelector('.denomination-total');
        totalElement.textContent = `Total: Rs ${total.toLocaleString()}`;
    }

    function updateTotalCash() {
        const inputs = document.querySelectorAll('.denomination-input');
        let total = 0;
        inputs.forEach(input => {
            const value = parseInt(input.dataset.value);
            const count = parseInt(input.value) || 0;
            total += value * count;
            updateDenominationTotal(input);
        });
        document.getElementById('opening_cash').value = total;
    }

    function openCloseSessionModal(sessionId) {
        document.getElementById('closeSessionModal').classList.remove('hidden');
        document.getElementById('closeSessionForm').action = `/admin/sessions/${sessionId}/close`;
        // Load current denomination counts
        loadCurrentDenominations();
    }

    function closeCloseSessionModal() {
        document.getElementById('closeSessionModal').classList.add('hidden');
    }

    function loadCurrentDenominations() {
        // Fetch current denomination counts from the server
        fetch('{{ route("admin.cash-denominations.total") }}')
            .then(response => response.json())
            .then(data => {
                // Update denomination inputs with current counts
                Object.entries(data.denominations).forEach(([value, count]) => {
                    const input = document.querySelector(`#closeSessionModal input[name="denominations[${value}]"]`);
                    if (input) {
                        input.value = count;
                        updateDenominationTotal(input);
                    }
                });
                updateTotalCash();
            });
    }

    function updateDenominationTotal(input) {
        const value = parseInt(input.dataset.value);
        const count = parseInt(input.value) || 0;
        const total = value * count;
        const totalElement = input.closest('.border').querySelector('.denomination-total');
        totalElement.textContent = `Total: Rs ${total.toLocaleString()}`;
    }

    function updateTotalCash() {
        const inputs = document.querySelectorAll('#closeSessionModal .denomination-input');
        let total = 0;
        inputs.forEach(input => {
            const value = parseInt(input.dataset.value);
            const count = parseInt(input.value) || 0;
            total += value * count;
            updateDenominationTotal(input);
        });
        document.getElementById('closing_cash').value = total;
    }

    // Initialize when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        const denominationInputs = document.querySelectorAll('.denomination-input');
        denominationInputs.forEach(input => {
            input.addEventListener('change', updateTotalCash);
        });
        updateTotalCash();
    });
</script>
@endpush 