@props(['methods', 'selected' => null])

<div class="payment-methods-grid">
    @foreach($methods as $method)
        <div class="payment-method-item {{ $selected == $method->code ? 'selected' : '' }}"
             x-data="{ selected: @entangle('selected') }"
             @click="selected = '{{ $method->code }}'">
            <div class="payment-method-icon">
                @if($method->icon)
                    <img src="{{ $method->icon }}" alt="{{ $method->name }}">
                @else
                    <i class="fas fa-credit-card"></i>
                @endif
            </div>
            <div class="payment-method-info">
                <h4>{{ $method->name }}</h4>
                @if($method->description)
                    <p>{{ $method->description }}</p>
                @endif
            </div>
            <div class="payment-method-radio">
                <input type="radio" 
                       name="payment_method" 
                       value="{{ $method->code }}"
                       x-model="selected"
                       class="hidden">
                <div class="radio-indicator"></div>
            </div>
        </div>
    @endforeach
</div>

@push('styles')
<style>
    .payment-methods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }

    .payment-method-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-method-item:hover {
        border-color: #4299e1;
    }

    .payment-method-item.selected {
        border-color: #4299e1;
        background-color: #ebf8ff;
    }

    .payment-method-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }

    .payment-method-icon img {
        max-width: 100%;
        max-height: 100%;
    }

    .payment-method-info {
        flex: 1;
    }

    .payment-method-info h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
    }

    .payment-method-info p {
        margin: 0.25rem 0 0;
        font-size: 0.875rem;
        color: #718096;
    }

    .payment-method-radio {
        margin-left: 1rem;
    }

    .radio-indicator {
        width: 20px;
        height: 20px;
        border: 2px solid #e2e8f0;
        border-radius: 50%;
        position: relative;
    }

    .payment-method-item.selected .radio-indicator {
        border-color: #4299e1;
    }

    .payment-method-item.selected .radio-indicator::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        background-color: #4299e1;
        border-radius: 50%;
    }
</style>
@endpush 