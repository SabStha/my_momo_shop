<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CartView extends Component
{
    public $items = [];
    public $cartCount = 0;
    public $subtotal = 0;

    protected $listeners = ['cartUpdated' => 'loadCart'];

    public function mount()
    {
        $this->loadCart();

        // Sync JS displayCart() with what Livewire loaded from DB on initial render.
        // Without this the JS reads empty localStorage and shows "Your cart is empty"
        // while Livewire renders items from DB — they become out of sync.
        $this->dispatchBrowserEvent('livewire-cart-updated', [
            'items'    => array_values($this->items),
            'count'    => $this->cartCount,
            'subtotal' => $this->subtotal,
        ]);
    }

    public function loadCart()
    {
        if (Auth::check()) {
            $userCart = Auth::user()->getOrCreateCart();
            $raw = $userCart->cart_data ?? [];

            // Deduplicate by id, merging quantities for any repeated entries
            $seen = [];
            foreach ($raw as $item) {
                $id = (string) ($item['id'] ?? '');
                if ($id === '') continue;
                if (isset($seen[$id])) {
                    $seen[$id]['quantity'] += (int) ($item['quantity'] ?? 1);
                } else {
                    $seen[$id] = $item;
                }
            }
            $deduped = array_values($seen);

            // Persist deduped version if it differs from raw
            if (count($deduped) !== count($raw)) {
                $userCart->updateCart($deduped);
            }

            $this->items = $deduped;
            $this->syncTotals();
        }
    }

    public function updateQuantity($productId, $quantity)
    {
        if ((int) $quantity <= 0) {
            $this->removeItem($productId);
            return;
        }

        foreach ($this->items as &$item) {
            if ((string) $item['id'] === (string) $productId) {
                $item['quantity'] = (int) $quantity;
                break;
            }
        }

        $this->saveCart();
    }

    public function removeItem($productId)
    {
        $this->items = array_values(array_filter(
            $this->items,
            fn($item) => (string) $item['id'] !== (string) $productId
        ));
        $this->saveCart();
    }

    public function clearCart()
    {
        $this->items = [];
        $this->saveCart();
    }

    private function syncTotals()
    {
        $this->cartCount = count($this->items);
        $this->subtotal  = collect($this->items)->sum(fn($i) => $i['price'] * $i['quantity']);
    }

    private function saveCart()
    {
        if (Auth::check()) {
            $userCart = Auth::user()->getOrCreateCart();
            $userCart->updateCart($this->items);
            $this->syncTotals();

            // Sync localStorage + JS summary on the cart page
            $this->dispatchBrowserEvent('livewire-cart-updated', [
                'items'    => array_values($this->items),
                'count'    => $this->cartCount,
                'subtotal' => $this->subtotal,
            ]);

            // Refresh the navbar badge (CartManager component listens for this)
            $this->emit('cartUpdated');
        }
    }

    public function render()
    {
        return view('livewire.cart-view');
    }
}
