<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Resources\FinanceResource;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\FinanceResource\Pages\ListFinances;

class AdminPanelFinanceFeatureTest extends TestCase
{

    private Collection $financesAdmin;
    private Collection $financesOthers;
    private User $userAdmin;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->userAdmin = $this->authenticatedUser(['role' => 'ADMIN'], false);
        $this->financesAdmin = $this->createFinance(['user_id' => $this->userAdmin->id, 'type' => 'DEBIT', 'status' => 'SUCCESS'], 4);
        $userMerchant = $this->createUser(['role' => 'MERCHANT']);
        $this->financesOthers = $this->createFinance(['user_id' => $userMerchant->id, 'status' => 'SUCCESS'], 3);
    }

    /** @test */
    public function finance_menu_list_can_be_rendered()
    {
        $this->get(FinanceResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function finance_menu_list_can_show_list_of_finance()
    {
        Livewire::test(ListFinances::class)
            ->assertCanSeeTableRecords($this->financesAdmin)
            ->assertCanNotSeeTableRecords($this->financesOthers);
    }
}
