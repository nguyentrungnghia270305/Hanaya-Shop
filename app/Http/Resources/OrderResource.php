<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        $items = [];
        if ($this->relationLoaded('items') || !empty($this->items)) {
            foreach ($this->items ?? [] as $it) {
                $items[] = [
                    'id' => $it->id ?? null,
                    'product_id' => $it->product_id ?? null,
                    'name' => $it->name ?? null,
                    'quantity' => $it->quantity ?? null,
                    'price' => $it->price ?? null,
                ];
            }
        }

        return [
            'id' => $this->id,
            'order_number' => $this->order_number ?? $this->id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'total' => $this->total ?? $this->grand_total ?? null,
            'status' => $this->status,
            'tracking_number' => $this->tracking_number,
            'assigned_to' => $this->assigned_to,
            'items' => $items,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
