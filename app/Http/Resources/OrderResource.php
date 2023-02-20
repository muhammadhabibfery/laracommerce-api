<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            return $this->resourceToCustomer();

        return $this->resourceToMerchant($request);
    }

    /**
     * Order resource to merchant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    private function resourceToMerchant($request)
    {
        return [
            'id' => $this->id,
            'invoiceNumber' => $this->invoice_number,
            'totalPrice' => $this->whenLoaded('totalPriceProductsMerchant', function () {
                $totalPrice = 0;
                foreach ($this->totalPriceProductsMerchant as $product)
                    $totalPrice += $product->pivot->total_price;
                return currencyFormat($totalPrice);
            }),
            'status' => $this->status,
            'products' => $this->whenLoaded('products', fn () => ProductResource::collection($this->products)),
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user))
        ];
    }

    /**
     * Order resource to customer.
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    private function resourceToCustomer()
    {
        return [
            "invoiceNumber" => $this->invoice_number,
            "totalPrice" => currencyFormat($this->total_price),
            "coupons" => $this->when(isset($this->coupons), json_decode($this->coupons)),
            "courierServices" => $this->when(isset($this->courier_services), function () {
                $cs = json_decode($this->courier_services);
                array_pop($cs);
                return collect($cs)->map(function ($item, $key) {
                    return str_replace(',', ' ', $item);
                })->all();
            }),
            "status" => $this->status,
            "products" => $this->whenLoaded('products', fn () => ProductResource::collection($this->products))
        ];
    }
}
