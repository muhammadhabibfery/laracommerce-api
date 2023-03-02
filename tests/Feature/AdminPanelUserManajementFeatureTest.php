<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Models\City;
use App\Models\User;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelUserManajementFeatureTest extends TestCase
{
    private Collection $users;
    private User $userInactive;
    private User $userAdmin;
    private City $city;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->userAdmin = $this->authenticatedUser(['role' => 'ADMIN'], false);
        $this->users = $this->createUser(count: 8);
        $this->userInactive = $this->createUser(['role' => 'STAFF', 'status' => 'INACTIVE']);

        $this->city = City::factory()->create();
    }

    /** @test */
    public function users_menu_list_can_be_rendered()
    {
        $this->get(UserResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function user_menu_list_can_show_list_of_users()
    {
        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords($this->users);
    }

    /** @test */
    public function user_menu_list_can_search_user_by_username()
    {
        $user = $this->users->last();

        Livewire::test(ListUsers::class)
            ->searchTable($user->username)
            ->assertCanSeeTableRecords($this->users->where('username', $user->username))
            ->assertCanNotSeeTableRecords($this->users->where('username', '!==', $user->username));
    }

    /** @test */
    public function user_menu_list_can_filter_user_by_role()
    {
        $user = $this->users->last();

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords($this->users)
            ->filterTable('role', json_encode($user->role))
            ->assertCanSeeTableRecords($this->users->where('role', $user->role))
            ->assertCanNotSeeTableRecords($this->users->where('role', '!==', $user->role));
    }

    /** @test */
    public function user_menu_create_can_be_rendered()
    {
        $this->get(UserResource::getUrl('create'))
            ->assertSuccessful();
    }

    /** @test */
    public function user_menu_create_can_create_a_new_user()
    {
        $newData = User::factory(['city_id' => $this->city->id, 'role' => 'STAFF'])->make();

        Livewire::test(CreateUser::class)
            ->fillForm(array_merge($newData->toArray(), ['role' => head($newData->role)]))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', Arr::only($newData->toArray(), ['name', 'username', 'email', 'phone']))
            ->assertDatabaseCount('users', 11);
    }

    /** @test */
    public function user_menu_create_the_rules_validation_should_be_dispatched()
    {
        $user = $this->users->last();

        Livewire::test(CreateUser::class)
            ->fillForm(array_merge($user->toArray(), ['city_id' => 1000, 'role' => 'CUSTOMER']))
            ->call('create')
            ->assertHasFormErrors(['city_id' => 'exists', 'email' => 'unique', 'phone' => 'unique', 'nik' => 'unique', 'role' => 'in']);
    }

    /** @test */
    public function user_menu_edit_can_be_rendered()
    {
        $this->get(UserResource::getUrl('edit', ['record' => $this->userInactive]))
            ->assertSuccessful();
    }

    /** @test */
    public function user_menu_edit_can_retrieve_data()
    {
        Livewire::test(EditUser::class, ['record' => $this->userInactive->username])
            ->assertFormSet([
                'name' => $this->userInactive->name,
                'username' => $this->userInactive->username,
            ]);
    }

    /** @test */
    public function user_menu_edit_can_edit_selected_user()
    {
        $newData = User::factory(['name' => 'ringgo star', 'username' => 'ringgo-star', 'role' => 'STAFF'])->make();
        $newData = array_merge(Arr::only($newData->toArray(), ['name', 'username']), ['role' => 'ADMIN', 'city_id' => $this->city->id]);

        Livewire::test(EditUser::class, ['record' => $this->userInactive->username])
            ->fillForm($newData)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', Arr::except($newData, ['role']))
            ->assertDatabaseMissing('users', Arr::only($this->userInactive->toArray(), ['name', 'username']));
    }

    /** @test */
    public function user_menu_delete_can_delete_selected_user()
    {
        Livewire::test(ListUsers::class)
            ->callTableAction(DeleteAction::class, $this->userInactive);

        $this->assertDatabaseMissing('users', Arr::only($this->userInactive->toArray(), ['name', 'username']))
            ->assertDatabaseCount('users', 9);
    }

    /** @test */
    public function user_menu_view_can_be_rendered()
    {
        $user = $this->users->last();

        $this->get(UserResource::getUrl('view', ['record' => $user->username]))
            ->assertSuccessful();
    }

    /** @test */
    public function user_menu_view_can_retrieve_data_of_selected_user()
    {
        $user = $this->createUser(['city_id' => $this->city->id, 'role' => 'STAFF', 'created_by' => $this->userAdmin->id]);

        Livewire::test(ViewUser::class, ['record' => $user->username])
            ->assertFormSet(array_merge(['city_id' => $this->city->name, 'created_by' => $this->userAdmin->name], Arr::only($user->toArray(), ['name', 'username', 'emai', 'phone', 'address', 'status', 'role'])));
    }
}
