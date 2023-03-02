<?php

namespace Tests\Feature;

use App\Filament\Resources\WithdrawResource;
use App\Filament\Resources\WithdrawResource\Pages\EditWithdraw;
use App\Filament\Resources\WithdrawResource\Pages\ListWithdraws;
use App\Mail\MerchantWdMail;
use App\Models\Finance;
use App\Models\MerchantAccount;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

class AdminPanelWithdrawRequestFeatureTest extends TestCase
{
    private Collection $finances;
    private User $userAdmin;
    private User $userMerchant;
    private MerchantAccount $merchantAccount;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->userAdmin = $this->authenticatedUser(['role' => 'ADMIN'], false);
        $this->userMerchant = $this->createUser(['role' => 'MERCHANT', 'name' => 'John Lennon']);
        $banking = $this->createBanking();
        $this->merchantAccount = $this->createMerchantAccount(['banking_id' => $banking->id, 'user_id' => $this->userMerchant->id, 'balance' => 160000, 'name' => 'Beatles Store']);

        $this->createFinance(['user_id' => $this->userMerchant->id, 'type' => 'KREDIT', 'description' => 'withdraw', 'amount' => 50000, 'status' => 'REJECT', 'balance' => 160000]);
        $this->createFinance(['user_id' => $this->userMerchant->id, 'type' => 'KREDIT', 'description' => 'withdraw', 'amount' => 50000, 'status' => 'SUCCESS', 'balance' => 110000]);
        $this->merchantAccount->update(['balance' => 110000]);
        $this->createFinance(['user_id' => $this->userMerchant->id, 'type' => 'KREDIT', 'description' => 'withdraw', 'amount' => 50000, 'status' => 'PENDING', 'balance' => 60000]);
        $this->merchantAccount->update(['balance' => 60000]);
        $this->createFinance(['user_id' => $this->userMerchant->id, 'type' => 'DEBIT', 'description' => 'revenue', 'amount' => 50000, 'status' => 'SUCCESS', 'balance' => 60000]);
        $this->finances = Finance::where('description',  'withdraw')->get();
    }

    /** @test */
    public function withdraw_menu_list_can_be_rendered()
    {
        $this->get(WithdrawResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function withdraw_menu_list_can_show_list_of_withdraw_request()
    {
        Livewire::test(ListWithdraws::class)
            ->assertCanSeeTableRecords($this->finances->where('type', 'KREDIT')->where('status', 'PENDING')->where('description', 'withdraw'))
            ->assertCanNotSeeTableRecords($this->finances->where('type', '!=', 'KREDIT')->where('status', '!=', 'PENDING')->where('description', '!=', 'withdraw'))
            ->assertCanNotSeeTableRecords($this->finances->where('type', 'KREDIT')->where('status', '!=', 'PENDING')->where('description', 'withdraw'));
    }

    /** @test */
    public function withdraw_menu_list_can_filter_withdraw_request_by_status()
    {
        Livewire::test(ListWithdraws::class)
            ->assertCanSeeTableRecords($this->finances->where('type', 'KREDIT')->where('status', 'PENDING')->where('description', 'withdraw'))
            ->filterTable('status', 'SUCCESS')
            ->assertCanSeeTableRecords($this->finances->where('type', 'KREDIT')->where('status', 'SUCCESS')->where('description', 'withdraw'))
            ->assertCanNotSeeTableRecords($this->finances->where('type', 'KREDIT')->where('status', '!=', 'SUCCESS')->where('description', 'withdraw'));
    }

    /** @test */
    public function withdraw_menu_edit_can_be_rendered()
    {
        $record = $this->finances->where('type', 'KREDIT')->where('status', 'PENDING')->where('description', 'withdraw')->first();
        $record->fill(['user_name' => $this->userMerchant->name, 'user_merchantAccount_name' => $this->userMerchant->merchantAccount->name]);

        $this->get(WithdrawResource::getUrl('edit', ['record' => $record]))
            ->assertSuccessful();
    }

    /** @test */
    public function withdraw_menu_edit_can_retrieve_data()
    {
        $record = $this->finances->where('type', 'KREDIT')->where('status', 'PENDING')->where('description', 'withdraw')->first();
        $record->fill(['user_name' => $this->userMerchant->name, 'user_merchantAccount_name' => $this->userMerchant->merchantAccount->name]);

        Livewire::test(EditWithdraw::class, ['record' => $record->id])
            ->assertFormSet([
                'user_name' => $record->user_name,
                'user_merchantAccount_name' => $record->user_merchantAccount_name,
                'amount' => currencyFormat($record->amount),
                'is_confirm' => null
            ]);
    }

    /** @test */
    public function withdraw_menu_edit_can_reject_selected_withdraw_request()
    {
        Mail::fake();
        $record = $this->finances->where('type', 'KREDIT')->where('status', 'PENDING')->where('description', 'withdraw')->first();
        $total = $record->balance + $record->amount;

        Livewire::test(EditWithdraw::class, ['record' => $record->id])
            ->fillForm(['is_confirm' => 0])
            ->call('save')
            ->assertHasNoFormErrors()->assertRedirect(WithdrawResource::getUrl('index'));
        Mail::assertQueued(MerchantWdMail::class);

        $this->assertDatabaseHas(
            'merchant_accounts',
            Arr::except(
                $this->merchantAccount->fill(['balance' => $total])->toArray(),
                ['created_at', 'updated_at']
            )
        )->assertDatabaseHas(
            'finances',
            Arr::except(
                $record->fill(['status' => 'REJECT', 'balance' => $total, 'updated_by' => $this->userAdmin->id])->toArray(),
                ['created_at', 'updated_at']
            )
        );
    }

    /** @test */
    public function withdraw_menu_edit_can_approve_selected_withdraw_request()
    {
        Mail::fake();
        $record = $this->finances->where('type', 'KREDIT')->where('status', 'PENDING')->where('description', 'withdraw')->first();

        Livewire::test(EditWithdraw::class, ['record' => $record->id])
            ->fillForm(['is_confirm' => 1])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(WithdrawResource::getUrl('index'));
        Mail::assertQueued(MerchantWdMail::class);

        $this->assertDatabaseHas(
            'merchant_accounts',
            Arr::except(
                $this->merchantAccount->toArray(),
                ['created_at', 'updated_at']
            )
        )->assertDatabaseHas(
            'finances',
            Arr::except(
                $record->fill(['status' => 'SUCCESS', 'updated_by' => $this->userAdmin->id])->toArray(),
                ['created_at', 'updated_at']
            )
        );
    }
}
