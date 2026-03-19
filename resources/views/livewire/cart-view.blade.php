<div>
    @if (count($items) === 0)
        <div class="p-8 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
            <p class="text-gray-600 mb-6">Looks like you haven't added any items to your cart yet.</p>
            <a href="/" class="inline-flex items-center px-4 py-2 bg-[#6E0D25] text-white rounded-lg hover:bg-[#8B0D2F] transition-colors">
                Start Shopping
            </a>
        </div>
    @else
        @foreach ($items as $item)
            <div wire:key="cart-item-{{ $item['id'] }}" class="p-6 border-b border-gray-200 last:border-b-0">
                <div class="flex items-center gap-4">

                    {{-- Product image --}}
                    <div class="flex-shrink-0">
                        @if (!empty($item['image']))
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                                 class="w-16 h-16 object-cover rounded-lg">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Name & unit price --}}
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</h3>
                        <p class="text-sm text-gray-500">Rs.{{ number_format($item['price'], 2) }} each</p>
                    </div>

                    {{-- Quantity stepper --}}
                    <div class="flex items-center gap-2">
                        <button wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                wire:loading.attr="disabled"
                                wire:target="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors disabled:opacity-50">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                        </button>

                        <span class="w-12 text-center text-sm font-medium text-gray-900">
                            {{ $item['quantity'] }}
                        </span>

                        <button wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                wire:loading.attr="disabled"
                                wire:target="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors disabled:opacity-50">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Line total & remove --}}
                    <div class="text-right">
                        <p class="text-sm font-bold text-[#6E0D25]">
                            Rs.{{ number_format($item['price'] * $item['quantity'], 2) }}
                        </p>
                        <button wire:click="removeItem('{{ $item['id'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="removeItem('{{ $item['id'] }}')"
                                onclick="return confirm('Remove {{ addslashes($item['name']) }} from cart?')"
                                class="text-xs text-red-600 hover:text-red-800 transition-colors mt-1 disabled:opacity-50">
                            Remove
                        </button>
                    </div>

                </div>
            </div>
        @endforeach

        {{-- Clear cart --}}
        <div class="p-4 border-t border-gray-100 text-right">
            <button wire:click="clearCart()"
                    wire:loading.attr="disabled"
                    onclick="return confirm('Clear your entire cart?')"
                    class="text-xs text-gray-500 hover:text-red-600 transition-colors disabled:opacity-50">
                Clear cart
            </button>
        </div>
    @endif
</div>
