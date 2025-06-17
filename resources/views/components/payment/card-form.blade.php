@props(['order'])

<div x-data="cardPaymentForm({{ $order->id }}, {{ $order->total }})" class="bg-white rounded-lg shadow-md p-6">
    <form @submit.prevent="submitPayment" class="space-y-6">
        <div>
            <label for="card_number" class="block text-sm font-medium text-gray-700">Card Number</label>
            <div class="mt-1 relative">
                <input type="text" 
                       id="card_number" 
                       x-model="form.card_number" 
                       x-on:input="formatCardNumber"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="1234 5678 9012 3456"
                       maxlength="19"
                       required>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <span x-show="form.card_brand" class="text-gray-400">
                        <i :class="getCardIcon"></i>
                    </span>
                </div>
            </div>
            <div x-show="errors.card_number" class="mt-1 text-sm text-red-600" x-text="errors.card_number"></div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="card_expiry" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                <div class="mt-1">
                    <input type="text" 
                           id="card_expiry" 
                           x-model="form.card_expiry" 
                           x-on:input="formatExpiry"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="MM/YY"
                           maxlength="5"
                           required>
                </div>
                <div x-show="errors.card_expiry" class="mt-1 text-sm text-red-600" x-text="errors.card_expiry"></div>
            </div>

            <div>
                <label for="card_cvv" class="block text-sm font-medium text-gray-700">CVV</label>
                <div class="mt-1">
                    <input type="text" 
                           id="card_cvv" 
                           x-model="form.card_cvv" 
                           x-on:input="formatCVV"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="123"
                           maxlength="3"
                           required>
                </div>
                <div x-show="errors.card_cvv" class="mt-1 text-sm text-red-600" x-text="errors.card_cvv"></div>
            </div>
        </div>

        <div>
            <label for="card_name" class="block text-sm font-medium text-gray-700">Cardholder Name</label>
            <div class="mt-1">
                <input type="text" 
                       id="card_name" 
                       x-model="form.card_name" 
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="John Doe"
                       required>
            </div>
            <div x-show="errors.card_name" class="mt-1 text-sm text-red-600" x-text="errors.card_name"></div>
        </div>

        <div class="mt-4">
            <div class="text-lg font-semibold text-gray-900">Total Amount: {{ number_format($amount, 2) }} {{ $currency }}</div>
        </div>

        <div class="mt-4">
            <button type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    :disabled="isProcessing">
                <span x-show="!isProcessing">Pay Now</span>
                <span x-show="isProcessing">Processing...</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function cardPaymentForm(orderId, amount) {
    return {
        orderId,
        amount,
        isProcessing: false,
        form: {
            card_number: '',
            card_expiry: '',
            card_cvv: '',
            card_name: '',
            card_brand: ''
        },
        errors: {},

        get getCardIcon() {
            const icons = {
                visa: 'fab fa-cc-visa',
                mastercard: 'fab fa-cc-mastercard',
                amex: 'fab fa-cc-amex',
                discover: 'fab fa-cc-discover',
                diners: 'fab fa-cc-diners-club'
            };
            return icons[this.form.card_brand] || '';
        },

        formatCardNumber() {
            let value = this.form.card_number.replace(/\D/g, '');
            let formattedValue = '';
            
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            
            this.form.card_number = formattedValue;
            this.detectCardBrand();
        },

        formatExpiry() {
            let value = this.form.card_expiry.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2);
            }
            this.form.card_expiry = value;
        },

        formatCVV() {
            this.form.card_cvv = this.form.card_cvv.replace(/\D/g, '');
        },

        detectCardBrand() {
            const number = this.form.card_number.replace(/\D/g, '');
            
            if (/^4/.test(number)) {
                this.form.card_brand = 'visa';
            } else if (/^5[1-5]/.test(number)) {
                this.form.card_brand = 'mastercard';
            } else if (/^3[47]/.test(number)) {
                this.form.card_brand = 'amex';
            } else if (/^3(?:0[0-5]|[68])/.test(number)) {
                this.form.card_brand = 'diners';
            } else if (/^6(?:011|5)/.test(number)) {
                this.form.card_brand = 'discover';
            } else {
                this.form.card_brand = '';
            }
        },

        async submitPayment() {
            this.isProcessing = true;
            this.errors = {};

            try {
                // Initialize payment
                const initResponse = await fetch('/payments/initialize', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        order_id: this.orderId,
                        payment_method: 'credit_card',
                        amount: this.amount,
                        currency: '{{ $currency }}',
                        card_number: this.form.card_number.replace(/\s/g, ''),
                        card_expiry: this.form.card_expiry,
                        card_cvv: this.form.card_cvv,
                        cardholder_name: this.form.card_name
                    })
                });

                const initResult = await initResponse.json();

                if (!initResult.success) {
                    this.errors = initResult.errors || { general: initResult.message };
                    return;
                }

                // Process payment
                const processResponse = await fetch(`/payments/${initResult.data.payment_id}/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const processResult = await processResponse.json();

                if (processResult.success) {
                    window.location.href = `/orders/${processResult.data.order_id}/success`;
                } else {
                    this.errors = processResult.errors || { general: processResult.message };
                }
            } catch (error) {
                this.errors = { general: 'An error occurred while processing your payment.' };
            } finally {
                this.isProcessing = false;
            }
        }
    };
}
</script>
@endpush 