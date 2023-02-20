<?php

namespace App\Http\Requests\API;

use App\Models\Coupon;
use App\Rules\CheckQuantityProduct;
use App\Rules\ValidateCoupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        if ($this->routeIs('checkout.shipping'))
            return [
                'name' => ['required', 'string', 'min:3', 'max:100'],
                'address' => ['required', 'string'],
                'phone' => ['required', 'digits_between:11,13', Rule::unique('users', 'phone')->ignore($this->user())],
                'city_id' => ['required', 'exists:cities,id']
            ];

        if ($this->routeIs('checkout.coupon-validate')) {
            $coupon = $this->getCouponByName($this->coupon);

            return [
                'coupon' => ['required', 'string', 'exists:coupons,name', new ValidateCoupon($coupon)]
            ];
        }

        return [
            "data.*.courier" => ['required', "in:jne,tiki,pos"],
            "data.*.courierService" => ['sometimes', 'string'],
            "data.*.coupon" => Rule::forEach(function ($value, $attribute) {
                $coupon = $this->getCouponByName($value);
                return ['nullable', 'exists:coupons,name', new ValidateCoupon($coupon)];
            }),
            "data.*.cart" => ['required', "array"],
            "data.*.cart.*.id" => ['required', 'exists:products,id'],
            "data.*.cart.*.name" => ['required', 'exists:products,name'],
            "data.*.cart.*.price" => Rule::forEach(function ($value, $attribute) {
                $productId = getEachProductField($attribute, $this, 'id');
                return ['required', Rule::exists('products', 'price')->where('id', $productId)];
            }),
            "data.*.cart.*.weight" => Rule::forEach(function ($value, $attribute) {
                $productId = getEachProductField($attribute, $this, 'id');
                return ['required', Rule::exists('products', 'weight')->where('id', $productId)];
            }),
            "data.*.cart.*.quantity" => Rule::forEach(function ($value, $attribute) {
                $productId = getEachProductField($attribute, $this, 'id');
                $productName = getEachProductField($attribute, $this, 'name');
                return ['required', new CheckQuantityProduct($productId, $productName)];
            })
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->routeIs('checkout.shipping') && $this->has('provinceId') && $this->has('cityId'))
            $this->merge([
                'city_id' => $this->cityId
            ]);

        if ($this->routeIs('checkout.process') || $this->routeIs('checkout.submit')) {
            $test = ['data' => []];

            foreach ($this->data as $data_key => $data) {
                array_push($test['data'], $data);
                foreach ($data['cart'] as $cart_key => $cart) {
                    $price = $test['data'][$data_key]['cart'][$cart_key]['price'];
                    $test['data'][$data_key]['cart'][$cart_key]['price'] = integerFormat($price);
                }
            }
            $this->merge($test);
        }
    }

    /**
     * Get the coupon by name.
     *
     * @param  string|null $name
     * @return Coupon|null
     */
    public function getCouponByName(?string $name): Coupon|null
    {
        return Coupon::where('name', $name)->first();
    }
}
