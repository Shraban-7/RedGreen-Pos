<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_code' => $this->sale_code,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ] : null,
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'pricing' => [
                'subtotal' => (float) $this->subtotal,
                'discount_type' => $this->discount_type,
                'discount_value' => (float) $this->discount_value,
                'discount_amount' => (float) $this->discount_amount,
                'vat_percentage' => (float) $this->vat_percentage,
                'vat_amount' => (float) $this->vat_amount,
                'grand_total' => (float) $this->grand_total,
                'paid_amount' => (float) $this->paid_amount,
                'due_amount' => (float) $this->due_amount,
            ],
            'status' => $this->status->value,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
