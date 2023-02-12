<?php

namespace Tests\Validations;

trait FinanceValidation
{

    /** @test */
    public function all_fields_are_required()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('finance.wd'), [], $this->header);

        $res->assertUnprocessable()
            ->assertJsonCount(4, 'errors');
    }

    /** @test */
    public function the_merchant_account_name_and_or_banking_account_name_and_or_banking_account_number_should_be_valid()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('finance.wd'), ['name' => 'john doe', 'bankAccountName' => 'john lennon', 'bankAccountNumber' => '12345', 'amount' => 100000], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'The selected name is invalid.')
            ->assertJsonPath('errors.bankAccountName.0', 'The selected bank account name is invalid.')
            ->assertJsonPath('errors.bankAccountNumber.0', 'The selected bank account number is invalid.')
            ->assertJsonCount(3, 'errors');
    }

    /** @test */
    public function the_withdraw_request_amount_should_be_less_then_merchant_finance_balance()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('finance.wd'), ['name' => $this->merchantAccount->name, 'bankAccountName' => $this->merchantAccount->bank_account_name, 'bankAccountNumber' => "{$this->merchantAccount->bank_account_number}", 'amount' => 500000], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.amount.0', 'Your balance is not enough to withdraw funds.')
            ->assertJsonCount(1, 'errors');
    }
}
