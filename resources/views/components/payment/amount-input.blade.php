@props(['amount', 'total', 'showChange' => false])

<div class="payment-amount-section" x-data="{ 
    amount: @entangle('amount'),
    total: {{ $total }},
    get change() {
        return this.amount - this.total;
    }
}">
    <div class="amount-input-group">
        <label for="payment-amount">Payment Amount</label>
        <div class="input-wrapper">
            <span class="currency-symbol">$</span>
            <input type="number" 
                   id="payment-amount"
                   x-model="amount"
                   step="0.01"
                   min="{{ $total }}"
                   class="amount-input"
                   placeholder="Enter amount">
        </div>
    </div>

    @if($showChange)
        <div class="change-display" x-show="amount > total">
            <span>Change:</span>
            <span class="change-amount" x-text="'$' + change.toFixed(2)"></span>
        </div>
    @endif

    <div class="quick-amount-buttons">
        <button type="button" @click="amount = total">Exact Amount</button>
        <button type="button" @click="amount = Math.ceil(total)">Round Up</button>
        <button type="button" @click="amount = Math.ceil(total/10) * 10">Round to 10</button>
    </div>
</div>

@push('styles')
<style>
    .payment-amount-section {
        margin: 1.5rem 0;
    }

    .amount-input-group {
        margin-bottom: 1rem;
    }

    .amount-input-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #4a5568;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .currency-symbol {
        position: absolute;
        left: 1rem;
        color: #718096;
    }

    .amount-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 1.125rem;
        transition: border-color 0.3s ease;
    }

    .amount-input:focus {
        outline: none;
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .change-display {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem;
        background-color: #f7fafc;
        border-radius: 0.5rem;
        margin: 1rem 0;
    }

    .change-amount {
        font-weight: 600;
        color: #2d3748;
    }

    .quick-amount-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .quick-amount-buttons button {
        padding: 0.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        background-color: white;
        color: #4a5568;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .quick-amount-buttons button:hover {
        background-color: #f7fafc;
        border-color: #cbd5e0;
    }
</style>
@endpush 