<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_code' => $this->payment_code,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'amount' => (float) $this->amount,
            'method' => $this->method->value,
            'type' => $this->type,
            'notes' => $this->notes,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
