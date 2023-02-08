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
        $rules = [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'merchant_account_id' => ['required', 'integer', 'exists:merchant_accounts,id'],
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:1'],
            'weight' => ['required', 'integer', 'min:1'],
            'stock' => ['sometimes', 'integer', 'min:1'],
        ];

        if ($this->routeIs('products.store'))
            return array_merge(
                $rules,
                ['name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('products', 'name')->where(
                        fn ($query) => $query->where('merchant_account_id', $this->all()['merchant_account_id'])
                    )
                ]]
            );

        if ($this->routeIs('products.update'))
            return array_merge(
                $rules,
                ['name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('products', 'name')->where(
                        fn ($query) => $query->where('merchant_account_id', $this->all()['merchant_account_id'])
                    )->ignore($this->product->id)
                ]]
            );
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
            'category_id.required' => 'The category is required.',
        ];
    }

    /**
     * merge the validated request with additional data
     *
     * @return array
     */
    public function validatedProduct(): array
    {
        return $this->routeIs('products.store')
            ? array_merge(
                $this->validated(),
                ['slug' =>  str($this->validated()['name'])->slug()->value() . '-' . head(explode('-', $this->user()->username)) . rand(111, 999)]
            )
            : array_merge($this->validated(), ['slug' => $this->product->slug]);
    }
}
