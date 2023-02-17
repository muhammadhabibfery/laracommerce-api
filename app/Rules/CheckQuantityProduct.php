<?php

namespace App\Rules;

use App\Models\Product;
use Illuminate\Contracts\Validation\Rule;

class CheckQuantityProduct implements Rule
{
    public int $productId;
    public string $productName;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $productId, string $productName)
    {
        $this->productId = $productId;
        $this->productName = $productName;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return $value <= $this->getProductById($this->productId)->stock;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return "The {$this->productName} does not have enough stock.";
    }

    /**
     * Get the product by id.
     *
     * @param  int $productId
     * @return Product
     */
    private function getProductById(int $productId): Product
    {
        return Product::findOrFail($productId);
    }
}
