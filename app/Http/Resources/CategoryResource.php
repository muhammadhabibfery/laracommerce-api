<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'products' => $this->whenLoaded('products', fn () => $this->getProductsRelation())
        ];
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
