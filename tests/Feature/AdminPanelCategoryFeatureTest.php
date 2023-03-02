<?php

namespace Tests\Feature;

use Tests\TestCase;
use Livewire\Livewire;
use App\Filament\Resources\CategoryResource\Pages\ManageCategories;
use App\Models\Category;
use Filament\Pages\Actions\CreateAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class AdminPanelCategoryFeatureTest extends TestCase
{
    private Collection $category;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $userAdmin = $this->authenticatedUser(['role' => 'ADMIN'], false);
        $this->category = $this->createCategory(['created_by' => $userAdmin->id], 3);
    }

    /** @test */
    public function the_category_resource_can_be_rendered()
    {
        Livewire::test(ManageCategories::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($this->category);
    }

    /** @test */
    public function category_can_be_created()
    {
        $category = Category::factory()->make();

        $res = Livewire::test(ManageCategories::class)
            ->callPageAction(CreateAction::class, $category->toArray());

        $res->assertHasNoPageActionErrors();

        $this->assertDatabaseHas('categories', Arr::only($category->toArray(), ['name', 'slug']));
    }

    /** @test */
    public function category_can_be_updated()
    {
        $category = $this->category->first();

        $res = Livewire::test(ManageCategories::class)
            ->callTableAction('edit', $category, ['name' => 'test', 'slug' => 'test']);

        $res->assertHasNoPageActionErrors();
    }

    /** @test */
    public function category_can_be_deleted()
    {
        $res = Livewire::test(ManageCategories::class)
            ->callTableAction('delete', $this->category->last());

        $res->assertHasNoPageActionErrors();
    }
}
