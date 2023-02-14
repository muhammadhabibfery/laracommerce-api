<?php

namespace Tests\Validations;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait MerchantAccountValidation
{
    /** @test */
    public function all_fields_are_required()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('accounts.store'), [], $this->header);

        $res->assertUnprocessable()
            ->assertJsonCount(6, 'errors');
    }

    /** @test */
    public function the_merchant_account_name_field_should_be_unique()
    {
        $this->withExceptionHandling();

        $user = $this->createUser();
        $this->createMerchantAccount(['user_id' => $user->id, 'name' => 'John Lennon', 'bank_account_name' => 'Paul', 'bank_account_number' => 12345]);

        $res = $this->postJson(route('accounts.store'), ['banking_id' => $this->banking->id, 'name' => 'John Lennon', 'bank_account_name' => 'Paul Mccarthney', 'bank_account_number' => '123456'], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'The name has already been taken.');
    }

    /** @test */
    public function the_banking_id_field_should_be_exists()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('accounts.store'), ['banking_id' => 123, 'name' => 'John Lennon'], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.banking_id.0', 'The selected banking is invalid.');
    }

    /** @test */
    public function the_bank_account_name_and_account_number_should_be_unique()
    {
        $this->withExceptionHandling();

        $user = $this->createUser();
        $this->createMerchantAccount(['user_id' => $user->id, 'bank_account_name' => 'Paul', 'bank_account_number' => 12345]);

        $res = $this->postJson(route('accounts.store'), ['banking_id' => $this->banking->id, 'name' => 'John Lennon', 'bank_account_name' => 'Paul', 'bank_account_number' => '12345'], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.bank_account_name.0', 'The bank account name has already been taken.')
            ->assertJsonPath('errors.bank_account_number.0', 'The bank account number has already been taken.');
    }

    /** @test */
    public function the_image_field_should_be_follow_the_image_rules()
    {
        Storage::fake('merchant-images');
        $this->withExceptionHandling();
        $file = UploadedFile::fake()->create('test', 3000, 'txt');

        $res = $this->postJson(route('accounts.store'), ['banking_id' => $this->banking->id, 'name' => 'John Lennon', 'bank_account_name' => 'Paul', 'bank_account_number' => '12345', 'image' => $file], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.image.0', 'The image must be an image.')
            ->assertJsonPath('errors.image.1', 'The image must not be greater than 2500 kilobytes.');
    }

    /** @test */
    public function the_user_must_have_only_one_merchant_account()
    {
        $this->withExceptionHandling();

        $this->createMerchantAccount(['user_id' => $this->authenticatedUser->id]);

        $res = $this->postJson(route('accounts.store'), ['banking_id' => $this->banking->id, 'name' => 'John Lennon'], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.user_id.0', 'You already have merchant account.');
    }

    /** @test */
    public function the_merchant_who_have_merchant_account_cannot_create_merchant_account_again()
    {
        $this->withExceptionHandling();

        $this->createMerchantAccount(['user_id' => $this->authenticatedUser->id]);
        $this->authenticatedUser->update(['role' => 'MERCHANT']);

        $res = $this->postJson(route('accounts.store'), ['banking_id' => $this->banking->id, 'name' => 'John Lennon'], $this->header);

        $res->assertBadRequest()
            ->assertJsonPath('message', 'You already have merchant account.');
    }
}
