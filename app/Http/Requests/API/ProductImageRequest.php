<?php

namespace App\Http\Requests\API;

use App\Http\Controllers\API\ProductImageController;
use App\Traits\ImageHandler;
use Illuminate\Foundation\Http\FormRequest;

class ProductImageRequest extends FormRequest
{
    use ImageHandler;

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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'image' => ['required', 'file', 'image', 'max:2500']
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

        if ($this->has('productId')) {
            $this->merge([
                'product_id' => $request['productId']
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
            'product_id.exists' => 'The selected product is invalid.',
            'product_id.required' => 'The product is required.',
        ];
    }

    /**
     * merge the validated request with additional data
     *
     * @return array
     */
    public function validatedProductImage(): array
    {
        return array_merge(
            $this->validated(),
            ['name' => $this->setImageFile($this, ProductImageController::$directory)]
        );
    }
}
