<?php

namespace App\Http\Requests\API;

use App\Rules\CheckBalance;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return checkRole(['MERCHANT'], $this->user()->role);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'merchant_account_id' => ['required', 'integer', 'exists:merchant_accounts,id'],
            'discount_amount' => ['required', 'integer', 'min:1000', new CheckBalance($this->user()->merchantAccount)],
            'expired' => ['required', 'date_format:Y-m-d H:i', 'after:tomorrow'],
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'name')->where(
                    fn ($query) => $query->where('merchant_account_id', $this->all()['merchant_account_id'])
                )
            ]
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'merchant_account_id' => $this->user()->merchantAccount->id
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'expired.date_format' => 'The :attribute does not match the format :format (example: 2023-12-30 00:00).',
        ];
    }

    /**
     * merge the validated request with additional data
     *
     * @return array
     */
    public function validatedCoupon(): array
    {
        return array_merge(
            $this->validated(),
            ['slug' =>  str($this->validated()['name'])->slug()->value() . '-' . head(explode('-', $this->user()->username)) . rand(111, 999)]
        );
    }
}
