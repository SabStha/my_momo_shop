<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MerchandiseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'category' => $this->category,
            'model' => $this->model,
            'purchasable' => $this->purchasable,
            'status' => $this->status,
            'badge' => $this->badge,
            'badge_color' => $this->badge_color,
        ];
    }
} 