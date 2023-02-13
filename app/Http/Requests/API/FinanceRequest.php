<?php

namespace App\Http\Requests\API;

use App\Rules\Finance\CheckWithDrawAmount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinanceRequest extends FormRequest
{
    protected $financeBalance;

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
        if ($this->routeIs('finance.wd') && $this->isMethod('POST')) {
            $this->financeBalance =
                $this->user()
                ->merchantAccount
                ->balance;

            return [
                'name' => ['required', 'string', Rule::exists('merchant_accounts', 'name')->where('id', $this->user()->merchantAccount->id)],
                'bankAccountName' => ['required', 'string', Rule::exists('merchant_accounts', 'bank_account_name')->where('id', $this->user()->merchantAccount->id)],
                'bankAccountNumber' => ['required', 'string', Rule::exists('merchant_accounts', 'bank_account_number')->where('id', $this->user()->merchantAccount->id)],
                'amount' => ['required', 'integer', 'min:1', new CheckWithDrawAmount($this->financeBalance)]
            ];
        }
    }

    /**
     * merge the validated request with additional data
     *
     * @return array
     */
    public function validatedWD(): array
    {
        return [
            'amount' => $this->validated()['amount'],
            'balance' => $this->financeBalance
        ];
    }
}
