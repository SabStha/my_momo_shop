<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'item_name' => $this->item_name,
            'quantity' => (int) $this->quantity,
            'price' => number_format((float)$this->price, 2, '.', ''),
            'subtotal' => number_format((float)$this->subtotal, 2, '.', ''),
        ];
    }
}