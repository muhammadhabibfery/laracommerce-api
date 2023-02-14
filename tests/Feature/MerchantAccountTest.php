<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Banking;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Validations\MerchantAccountValidation;

class MerchantAccountTest extends TestCase
{
    use MerchantAccountValidation;

    public User $authenticatedUser;
    public Banking $banking;
    public string $directory = 'merchant-images';


    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->authenticatedUser = $this->authenticatedUser(['role' => 'CUSTOMER']);
        $this->banking = $this->createBanking();
    }

    private function setMerchantData($imageFile = null): array
    {
        return [
            'bankingId' => $this->banking->id,
            'name' => 'Furniture Store',
            'slug' => 'furniture-store',
            'address' => 'jl.',
            'bank_account_name' => 'John Lennon',
            'bank_account_number' => '123456',
            'bank_branch_name' => 'Citayem',
            'image' => $imageFile
        ];
    }

    /** @test */
    public function a_user_can_create_merchant_account()
    {
        Storage::fake($this->directory);
        $file = UploadedFile::fake()->image('beatles.png')->size(2000);
        $merchantAccount = $this->setMerchantData($file);

        $res = $this->postJson(route('accounts.store'), $merchantAccount, $this->header);

        $res->assertCreated()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            );

        $name = last(explode('/', $res->json()['data']['image']));
        $this->assertDatabaseHas('merchant_accounts', Arr::except(array_merge($merchantAccount, ['banking_id' => $this->banking->id]), ['bankingId', 'image']));
        $this->deleteDirectory($this->directory, $name);
    }

    /** @test */
    public function show_a_merchant_account()
    {
        $merchantData = array_merge(Arr::except($this->setMerchantData(), ['bankingId']), ['banking_id' => $this->banking->id, 'user_id' => $this->authenticatedUser->id]);
        $merchantAccount = $this->createMerchantAccount($merchantData);
        $this->authenticatedUser->update(['role' => 'MERCHANT']);

        $res = $this->getJson(route('accounts.show'), $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'data.bankingName', 'data.user'])
            )->assertJsonPath('data.name', $merchantAccount->name);

        $this->assertDatabaseCount('merchant_accounts', 1)
            ->assertDatabaseHas('merchant_accounts', Arr::except($merchantAccount->toArray(), ['image', 'created_at', 'updated_at']));
    }

    /** @test */
    public function a_user_can_update_merchant_account()
    {
        $this->withExceptionHandling();
        Storage::fake($this->directory);
        $file1 = UploadedFile::fake()->image('beatles.png');
        $file2 = UploadedFile::fake()->image('oasis.png');
        $merchantData = array_merge(Arr::except($this->setMerchantData($file1), ['bankingId']), ['banking_id' => $this->banking->id, 'user_id' => $this->authenticatedUser->id]);
        $merchantAccount = $this->createMerchantAccount($merchantData);
        $dataUpdate = array_merge(
            $merchantAccount->toArray(),
            ['bankingId' => $this->setMerchantData()['bankingId'], 'address' => 'jl.test', 'image' => $file2]
        );
        $this->authenticatedUser->update(['role' => 'MERCHANT']);

        $res = $this->putJson(route('accounts.update'), $dataUpdate, $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            );

        $name = last(explode('/', $res->json()['data']['image']));
        $this->assertDatabaseHas('merchant_accounts', Arr::except(array_merge($dataUpdate, ['banking_id' => $this->banking->id]), ['bankingId', 'image', 'created_at', 'updated_at']));
        $this->deleteDirectory($this->directory, $name);
    }
}
