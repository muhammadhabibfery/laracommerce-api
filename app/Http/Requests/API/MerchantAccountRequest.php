<?php

namespace App\Http\Requests\API;

use App\Http\Controllers\API\MerchantAccountController;
use App\Traits\ImageHandler;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MerchantAccountRequest extends FormRequest
{
    use ImageHandler;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'banking_id' => ['required', 'integer', 'exists:bankings,id'],
            'bank_branch_name' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string'],
            'image' => ['nullable', 'file', 'image', 'max:2500']
        ];

        if ($this->routeIs('accounts.store'))
            return array_merge(
                $rules,
                [
                    'user_id' => ['required', 'integer', 'exists:users,id', Rule::unique('merchant_accounts', 'user_id')->where(fn ($query) => $query->where('user_id', $this->user()->id))],
                    'name' => ['required', 'string', 'max:100', Rule::unique('merchant_accounts', 'name')],
                    'bank_account_name' => ['required', 'string', 'max:100', Rule::unique('merchant_accounts', 'bank_account_name')],
                    'bank_account_number' => ['required', 'string', 'max:50', Rule::unique('merchant_accounts', 'bank_account_number')]
                ]
            );

        if ($this->routeIs('accounts.update'))
            return array_merge(
                $rules,
                [
                    'user_id' => ['required', 'integer', 'exists:users,id', Rule::unique('merchant_accounts', 'user_id')->where(fn ($query) => $query->where('user_id', $this->user()->id))->ignore($this->user()->merchantAccount->id)],
                    'name' => ['required', 'string', 'max:100', Rule::unique('merchant_accounts', 'name')->ignore($this->user()->merchantAccount->id)],
                    'bank_account_name' => ['required', 'string', 'max:100', Rule::unique('merchant_accounts', 'bank_account_name')->ignore($this->user()->merchantAccount->id)],
                    'bank_account_number' => ['required', 'string', 'max:50', Rule::unique('merchant_accounts', 'bank_account_number')->ignore($this->user()->merchantAccount->id)]
                ]
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
        $this->merge(['user_id' => $this->user()->id]);

        if ($this->has('bankingId')) {
            $this->merge([
                'banking_id' => $request['bankingId'],
                'bankAccountName' => $request['bank_account_name'],
                'bankAccountNumber' => $request['bank_account_number'],
                'bankBranchName' => $request['bank_branch_name'],
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
            'banking_id.exists' => 'The selected banking is invalid.',
            'banking_id.required' => 'The banking field is required.',
            'user_id.unique' => 'You already have merchant account.',
        ];
    }

    /**
     * merge the validated request with additional data
     *
     * @return array
     */
    public function validatedMerchantAccount(): array
    {
        $request = $this->all();
        $oldImage = null;

        if ($this->routeIs('accounts.update')) $oldImage = $this->user()->merchantAccount->image;

        return array_merge(
            $this->validated(),
            ['image' => $this->setImageFile($this, MerchantAccountController::$directory, $oldImage)]
        );
    }
}
