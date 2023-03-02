<?php

namespace Tests\Feature;

use App\Filament\Resources\BankingResource;
use App\Filament\Resources\BankingResource\Pages\CreateBanking;
use App\Filament\Resources\BankingResource\Pages\EditBanking;
use App\Filament\Resources\BankingResource\Pages\ListBankings;
use App\Models\Banking;
use Tests\TestCase;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Livewire\Livewire;

class AdminPanelBankingFeatureTest extends TestCase
{
    private Collection $bankings;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $userAdmin = $this->authenticatedUser(['role' => 'ADMIN'], false);
        $this->bankings = $this->createBanking(['created_by' => $userAdmin->id], 10);
    }

    /** @test */
    public function banking_menu_list_can_be_rendered()
    {
        $this->get(BankingResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function banking_menu_list_can_show_list_of_bankings()
    {
        Livewire::test(ListBankings::class)
            ->assertCanSeeTableRecords($this->bankings);
    }

    /** @test */
    public function banking_menu_list_can_search_bankings_by_alias()
    {
        $banking = $this->bankings->first();

        Livewire::test(ListBankings::class)
            ->searchTable($banking->alias)
            ->assertCanSeeTableRecords($this->bankings->where('alias', $banking->alias))
            ->assertCanNotSeeTableRecords($this->bankings->where('alias', '!=', $banking->alias));
    }

    /** @test */
    public function banking_menu_create_can_be_rendered()
    {
        $this->get(BankingResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function banking_menu_create_can_create_a_new_banking()
    {
        $newData = Banking::factory()->make();

        Livewire::test(CreateBanking::class)
            ->fillForm(Arr::only(array_merge($newData->toArray(), ['alias' => 'xxx']), ['name', 'alias']))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('bankings', Arr::only($newData->toArray(), ['name']))
            ->assertDatabaseCount('bankings', 11);
    }

    /** @test */
    public function banking_menu_create_the_name_field_should_be_unique()
    {
        $banking = $this->bankings->first();

        Livewire::test(CreateBanking::class)
            ->fillForm([
                'name' => $banking->name,
                'alias' => 'xxx'
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    /** @test */
    public function banking_menu_edit_can_be_rendered()
    {
        $this->withExceptionHandling();
        $this->get(BankingResource::getUrl('edit', [
            'record' => $this->bankings->first()
        ]))
            ->assertSuccessful();
    }

    /** @test */
    public function banking_menu_edit_can_retrieve_data()
    {
        $banking = $this->bankings->last();

        Livewire::test(EditBanking::class, [
            'record' => $banking->alias
        ])
            ->assertFormSet([
                'name' => $banking->name,
                'alias' => $banking->alias
            ]);
    }

    /** @test */
    public function banking_menu_can_update_selected_banking()
    {
        $banking = $this->bankings->last();
        $newData = Banking::factory(['alias' => 'xxx'])
            ->make();

        Livewire::test(EditBanking::class, [
            'record' => $banking->alias
        ])
            ->fillForm(Arr::only($newData->toArray(), ['name', 'alias']))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('bankings', Arr::only($newData->toArray(), ['name', 'alias']))
            ->assertDatabaseMissing('bankings', Arr::only($banking->toArray(), ['name', 'alias']));
    }

    /** @test */
    public function banking_menu_delete_can_delete_selected_banking()
    {
        $banking = $this->bankings->last();

        Livewire::test(ListBankings::class)
            ->callTableAction(DeleteAction::class, $banking);

        $this->assertDatabaseMissing('bankings', Arr::only($banking->toArray(), ['name', 'alias']));
    }
}
