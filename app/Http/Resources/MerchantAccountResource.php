<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use App\Http\Resources\BankingResource;
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'bankAccountName' => $this->bank_account_name,
            'bankAccountNumber' => $this->bank_account_number,
            'bankBranchName' => $this->bank_branch_name,
            'image' => $this->getImage(),
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'bankingName' => $this->whenLoaded('banking', fn () => $this->banking->name),
            'financeBalance' => $this->whenLoaded(
                'banking',
                fn () => currencyFormat($this->user->finance()->latest()->first()?->balance ?: 0)
            )
        ];
    }
}
