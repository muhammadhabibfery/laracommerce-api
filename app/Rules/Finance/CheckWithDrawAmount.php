<?php

namespace App\Rules\Finance;

use Illuminate\Contracts\Validation\Rule;

class CheckWithDrawAmount implements Rule
{
    /**
     * The data under validation.
     *
     * @var array
     */
    protected $balance;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($balance)
    {
        $this->balance = $balance;
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
        return (int) $value < $this->balance;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Your balance is not enough to withdraw funds.';
    }
}
