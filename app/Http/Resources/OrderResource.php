<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'type' => $this->type,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'total_amount' => number_format((float)$this->total_amount, 2, '.', ''),
            'tax_amount' => number_format((float)$this->tax_amount, 2, '.', ''),
            'grand_total' => number_format((float)$this->grand_total, 2, '.', ''),
            'guest_name' => $this->guest_name,
            'guest_email' => $this->when(
                $request->user()?->hasAnyRole(['admin', 'cashier']),
                $this->guest_email
            ),
            'table' => new TableResource($this->whenLoaded('table')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            
            // Only show financial details to authorized users
            'amount_received' => $this->when(
                $request->user()?->hasAnyRole(['admin', 'cashier']),
                $this->amount_received ? number_format((float)$this->amount_received, 2, '.', '') : null
            ),
            'change' => $this->when(
                $request->user()?->hasAnyRole(['admin', 'cashier']),
                $this->change ? number_format((float)$this->change, 2, '.', '') : null
            ),
        ];
    }
}