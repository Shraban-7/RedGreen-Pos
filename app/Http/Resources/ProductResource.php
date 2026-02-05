<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'category' => [
                'id'   => $this->category_id,
                'name' => optional($this->category)->name,
            ],

            'brand' => $this->brand_id ? [
                'id'   => $this->brand_id,
                'name' => optional($this->brand)->name,
            ] : null,

            'seller' => [
                'id'   => $this->user_id,
                'name' => optional($this->user)->name,
            ],

            'name' => $this->name,
            'slug' => $this->slug,
            'sku'  => $this->sku,

            'description' => $this->description,

            'thumbnail' => $this->thumbnail
                ? storage_url($this->thumbnail)
                : null,

            'pricing' => [
                'buying_price'     => (float) $this->buying_price,
                'selling_price'    => (float) $this->selling_price,
                'discount_type'    => $this->discount_type,
                'discount_value'   => $this->discount_value,
                'discount_amount'  => (float) $this->discount_amount,
                'discounted_price' => (float) $this->discounted_price,
            ],

            'stock' => [
                'stock_in'           => $this->stock_in,
                'stock_out'          => $this->stock_out,
                'available_quantity' => max($this->stock_in - $this->stock_out, 0),
                'low_stock_quantity' => $this->low_stock_quantity,
                'is_low_stock'       => ($this->stock_in - $this->stock_out) <= $this->low_stock_quantity,
            ],

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
