<?php

namespace App\Http\Resources;

use Illuminate\Support\Arr;
use App\Http\Resources\UserResource;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MerchantAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $resource = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'bankAccountName' => $this->bank_account_name,
            'bankAccountNumber' => $this->bank_account_number,
            'bankBranchName' => $this->bank_branch_name,
            'image' => $this->getImage(),
            'bankingName' => $this->whenLoaded('banking', fn () => $this->banking->name),
            'balance' => currencyFormat($this->balance ?: 0),
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user))
        ];

        if ($request->is('api/merchant/detail*') || !$request->is('api/merchant*'))
            return $this->resourceToCustomer($request, $resource);

        return $resource;
    }

    /**
     * Merchant account resource to customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    private function resourceToCustomer($request, $resource)
    {
        $resource = Arr::except($resource, ['bankAccountName', 'bankAccountNumber', 'bankBranchName', 'balance']);

        $resource = array_merge(
            $resource,
            ['products' => $this->whenLoaded('products', fn () => $this->getProductsRelation())]
        );

        return $resource;
    }

    /**
     * Get the products relation and set into an array.
     *
     * @return array
     */
    private function getProductsRelation(): array
    {
        $products = ProductResource::collection($this->products()->paginate(6)->appends(request()->query()))
            ->response()
            ->getData(true);
        return  [
            'data' => $products['data'],
            'pages' => ['links' => $products['links'], 'meta' => $products['meta']]
        ];
    }
}
