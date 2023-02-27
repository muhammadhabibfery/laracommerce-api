<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Livewire\Livewire;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use App\Filament\Resources\ProfileResource;
use App\Filament\Resources\ProfileResource\Pages\CreateProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminPanelProfileFeatureTest extends TestCase
{
    private User $userAdmin;
    private string $directory = 'avatars';


    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        Session::setDefaultDriver('array');
        $this->userAdmin = $this->authenticatedUser(['role' => 'ADMIN'], false);
    }

    /** @test */
    public function profile_menu_can_be_rendered()
    {
        $this->get(ProfileResource::getUrl('index'))
            ->assertSuccessful();
    }

    /** @test */
    public function profile_menu_can_retrieve_data()
    {
        Livewire::test(CreateProfile::class)
            ->assertFormSet(Arr::only($this->userAdmin->toArray(), ['name', 'username', 'phone', 'email']));
    }

    /** @test */
    public function profile_menu_the_rules_validation_should_be_dispatched()
    {
        $user = $this->createUser();
        $user = Arr::only($user->toArray(), ['email', 'phone']);

        Livewire::test(CreateProfile::class)
            ->fillForm($user)
            ->call('create')
            ->assertHasFormErrors(['email' => 'unique', 'phone' => 'unique']);
    }

    /** @test */
    public function profile_menu_can_update_the_user_profile()
    {
        $newData = User::factory(['name' => 'Paul Mccarthney', 'username' => 'paul-mccarthney'])->make();
        $newData = array_merge($this->userAdmin->toArray(), ['name' => 'Paul Lennon', 'username' => 'paul-lennon']);

        Livewire::test(CreateProfile::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', Arr::only($newData, ['name', 'username']));
    }

    /** @test */
    public function profile_menu_can_upload_user_avatar()
    {
        Storage::fake($this->directory);
        $image = UploadedFile::fake()->image('beatles.jpg');
        $newData = User::factory(['name' => 'Paul Mccarthney', 'username' => 'paul-mccarthney'])->make();
        $newData = array_merge($this->userAdmin->toArray(), ['name' => 'Paul Lennon', 'username' => 'paul-lennon', 'avatar' => $image]);

        $res = Livewire::test(CreateProfile::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $image = $res->json()->payload['serverMemo']['data']['data']['avatar'][0];

        $this->assertDatabaseHas('users', ['avatar' => $image]);
        $this->deleteDirectory($this->directory, last(explode('/', $image)));
    }

    /** @test */
    public function profile_menu_can_change_password_user()
    {
        $this->withSession(['password_hash_' . Auth::getDefaultDriver() => $this->userAdmin->getAuthPassword()]);
        $password = ['current_password' => 'password@123', 'new_password' => 'password@121', 'new_password_confirmation' => 'password@121'];

        Livewire::test(CreateProfile::class)
            ->fillForm($password)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertSessionHas(['password_hash_' . Auth::getDefaultDriver() => $this->userAdmin->getAuthPassword()]);
    }
}
