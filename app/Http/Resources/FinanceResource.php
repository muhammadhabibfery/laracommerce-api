<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinanceResource extends JsonResource
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
            'type' => $this->type,
            'orderId' => $this->order_id,
            'description' => $this->description,
            'amount' => currencyFormat($this->amount),
            'status' => $this->status,
            'balance' => currencyFormat($this->balance),
            'createdAt' => transformDateFormat($this->created_at, 'Y-m-d H:i'),
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user))
        ];
    }
}
