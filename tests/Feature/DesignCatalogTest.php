<?php

use App\Models\Category;
use App\Models\Design;
use Livewire\Livewire;

it('shows every active design by default', function () {
    $category = Category::factory()->create(['name' => 'Athletic Jersey', 'slug' => 'athletic-jersey']);

    Design::factory()->for($category)->create(['name' => 'Basketball Jersey - Home.png']);
    Design::factory()->for($category)->inactive()->create(['name' => 'Retired Design.png']);

    Livewire::test('design-catalog')
        ->assertSee('Basketball Jersey - Home.png')
        ->assertSee('Athletic Jersey')
        ->assertDontSee('Retired Design.png')
        ->assertSet('category', 'all');
});

it('filters designs when a category is selected', function () {
    $jerseys = Category::factory()->create(['name' => 'Athletic Jersey', 'slug' => 'athletic-jersey']);
    $hoodies = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    Design::factory()->for($jerseys)->create(['name' => 'Basketball Jersey - Home.png']);
    Design::factory()->for($hoodies)->create(['name' => 'Pullover Hoodie - Front.png']);

    Livewire::test('design-catalog')
        ->set('category', 'hoodies')
        ->assertSee('Pullover Hoodie - Front.png')
        ->assertDontSee('Basketball Jersey - Home.png');
});

it('searches designs by filename', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    Design::factory()->for($category)->create(['name' => 'Pullover Hoodie - Front.png']);
    Design::factory()->for($category)->create(['name' => 'Team Bomber - Back.png']);

    Livewire::test('design-catalog')
        ->set('search', 'bomber')
        ->assertSee('Team Bomber - Back.png')
        ->assertDontSee('Pullover Hoodie - Front.png');
});

it('searches designs by category name', function () {
    $hoodies = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);
    $jerseys = Category::factory()->create(['name' => 'Athletic Jersey', 'slug' => 'athletic-jersey']);

    Design::factory()->for($hoodies)->create(['name' => 'Pullover Hoodie - Front.png']);
    Design::factory()->for($jerseys)->create(['name' => 'Basketball Jersey - Home.png']);

    Livewire::test('design-catalog')
        ->set('search', 'hoodies')
        ->assertSee('Pullover Hoodie - Front.png')
        ->assertDontSee('Basketball Jersey - Home.png');
});

it('shows an empty state and can reset the filters', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    Design::factory()->for($category)->create(['name' => 'Pullover Hoodie - Front.png']);

    Livewire::test('design-catalog')
        ->set('search', 'does-not-exist')
        ->assertSee('No designs match that search.')
        ->call('clearFilters')
        ->assertSet('category', 'all')
        ->assertSet('search', '')
        ->assertSee('Pullover Hoodie - Front.png');
});

it('ignores an unknown category filter', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    Design::factory()->for($category)->create(['name' => 'Pullover Hoodie - Front.png']);

    Livewire::test('design-catalog')
        ->set('category', 'not-a-category')
        ->assertSee('Pullover Hoodie - Front.png');
});

it('shows the design library page', function () {
    $category = Category::factory()->create(['name' => 'Athletic Jersey', 'slug' => 'athletic-jersey']);

    Design::factory()->for($category)->create([
        'name' => 'Basketball Jersey - Home.png',
        'drive_url' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view?usp=sharing',
    ]);

    $this->get(route('designs'))
        ->assertOk()
        ->assertSee('Basketball Jersey - Home.png')
        ->assertSee('Athletic Jersey')
        ->assertSee('https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view?usp=sharing')
        ->assertSee('Designs');
});

it('paginates designs one hundred per page', function () {
    $category = Category::factory()->create(['name' => 'Athletic Jersey', 'slug' => 'athletic-jersey']);

    Design::factory()->for($category)->count(101)->sequence(
        fn ($sequence) => [
            'name' => 'Design '.($sequence->index + 1).'.png',
            'sort_order' => $sequence->index,
        ],
    )->create();

    Livewire::test('design-catalog')
        ->assertSee('Design 1.png')
        ->assertSee('Design 100.png')
        ->assertDontSee('Design 101.png')
        ->assertSee('1–100')
        ->assertSeeHtml('wire:click="nextPage')
        ->call('gotoPage', 2)
        ->assertSee('Design 101.png')
        ->assertDontSee('Design 1.png')
        ->assertSee('101–101')
        ->set('search', 'Design')
        ->assertSet('paginators.page', 1)
        ->assertSee('Design 1.png');
});
