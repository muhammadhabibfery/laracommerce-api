<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'merchant_account_id' => ['required', 'integer', 'exists:merchant_accounts,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'name')->where(
                    fn ($query) => $query->whereNot('merchant_account_id', $this->all()['merchant_account_id'])
                )->ignore($this->user()),
            ],
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:1'],
            'weight' => ['required', 'integer', 'min:1'],
            'stock' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $request = $this->all();

        if ($this->has('categoryId')) {
            $this->merge([
                'category_id' => $request['categoryId'],
                'merchant_account_id' => $this->user()->merchantAccount->id
            ]);
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category is invalid.',
        ];
    }
}
