<?php

namespace App\Http\Livewire;
use Livewire\Component;
use App\Models\UserCart;
use Illuminate\Support\Facades\Auth;

class CartManager extends Component
{
    public $items = [];
    public $cartCount = 0;
    public $subtotal = 0;

    // Listen for browser events dispatched by JS cart operations
    protected $listeners = ['cartUpdated' => 'loadCart'];

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        if (Auth::check()) {
            $userCart = Auth::user()->getOrCreateCart();
            $this->items = $userCart->cart_data ?? [];
            $this->cartCount = count($this->items);
            $this->subtotal = collect($this->items)->sum(fn($i) => $i['price'] * $i['quantity']);
        }
    }

    public function addItem($productId, $name, $price, $image = null)
    {
        if (!Auth::check()) return;

        $found = false;
        foreach ($this->items as &$item) {
            if ((string)$item['id'] === (string)$productId) {
                $item['quantity']++;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->items[] = [
                'id' => (string)$productId,
                'name' => $name,
                'price' => $price,
                'quantity' => 1,
                'image' => $image,
                'type' => 'product'
            ];
        }

        $this->saveCart();
    }

    public function updateQuantity($productId, $quantity)
    {
        foreach ($this->items as &$item) {
            if ((string)$item['id'] === (string)$productId) {
                $item['quantity'] = max(1, (int)$quantity);
                break;
            }
        }
        $this->saveCart();
    }

    public function removeItem($productId)
    {
        $this->items = array_values(array_filter(
            $this->items,
            fn($item) => (string)$item['id'] !== (string)$productId
        ));
        $this->saveCart();
    }

    public function clearCart()
    {
        $this->items = [];
        $this->saveCart();
    }

    private function saveCart()
    {
        if (Auth::check()) {
            $userCart = Auth::user()->getOrCreateCart();
            $userCart->updateCart($this->items);
            $this->cartCount = count($this->items);
            $this->subtotal = collect($this->items)->sum(fn($i) => $i['price'] * $i['quantity']);

            // Notify JS listeners (e.g. cart page summary) that the cart changed
            $this->dispatchBrowserEvent('cart-updated', [
                'count'    => $this->cartCount,
                'subtotal' => $this->subtotal,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.cart-manager');
    }
}
