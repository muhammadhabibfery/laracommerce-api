<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class ValidateCoupon implements Rule
{
    public $coupon;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($coupon)
    {
        $this->coupon = $coupon;
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
        if (isset($this->coupon))
            return $this->coupon->expired->format(config('app.date_format')) >= Carbon::parse(now(config('app.timezone')))->format(config('app.date_format'));
        else
            return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The coupon has been expired';
    }
}
