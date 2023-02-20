<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if ($request->is('api/orders*'))
            return [
                'name' => $this->name,
                'description' => $this->description,
                'price' => currencyFormat($this->price),
                'weight' => $this->weight,
                'quantity' => $this->pivot->quantity,
            ];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => currencyFormat($this->price),
            'weight' => $this->weight,
            'stock' => $this->stock,
            'sold' => $this->sold ?: 0,
            'quantity' => $this->whenPivotLoaded('order_product', fn () => $this->pivot->quantity),
            'totalPrice' => $this->whenPivotLoaded('order_product', fn () => currencyFormat($this->pivot->total_price)),
            'merchant' => $this->whenLoaded('merchantAccount', fn () => new MerchantAccountResource($this->merchantAccount)),
            'category' => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
        ];
    }
}
