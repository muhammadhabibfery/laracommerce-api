<?php

namespace App\Rules;

use App\Models\MerchantAccount;
use Illuminate\Contracts\Validation\Rule;

class CheckBalance implements Rule
{
    public MerchantAccount $merchantAccount;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($merchantAccount)
    {
        $this->merchantAccount = $merchantAccount;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->merchantAccount->balance > $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Your balance is not enough to create the coupon';
    }
}
