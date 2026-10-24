<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

it('shows every active product by default', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    Product::factory()->for($category)->create(['name' => 'Everyday Crewneck']);
    Product::factory()->for($category)->inactive()->create(['name' => 'Hidden Crewneck']);

    Livewire::test('product-catalog')
        ->assertSee('Everyday Crewneck')
        ->assertDontSee('Hidden Crewneck')
        ->assertSet('category', 'all');
});

it('filters products when a category is selected', function () {
    $hoodies = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);
    $jackets = Category::factory()->create(['name' => 'Jackets', 'slug' => 'jackets']);

    Product::factory()->for($hoodies)->create(['name' => 'Everyday Crewneck']);
    Product::factory()->for($jackets)->create(['name' => 'Team Bomber Jacket']);

    Livewire::test('product-catalog')
        ->set('category', 'hoodies')
        ->assertSee('Everyday Crewneck')
        ->assertDontSee('Team Bomber Jacket');
});

it('searches products by name and description', function () {
    $category = Category::factory()->create(['name' => 'Jackets', 'slug' => 'jackets']);

    Product::factory()->for($category)->create([
        'name' => 'Team Bomber Jacket',
        'description' => 'A lightweight shell for travel days.',
    ]);

    Product::factory()->for($category)->create([
        'name' => 'Classic Leather Jacket',
        'description' => 'Heavy leather for the road.',
    ]);

    Livewire::test('product-catalog')
        ->set('search', 'bomber')
        ->assertSee('Team Bomber Jacket')
        ->assertDontSee('Classic Leather Jacket');
});

it('shows an empty state and can reset the filters', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    Product::factory()->for($category)->create(['name' => 'Everyday Crewneck']);

    Livewire::test('product-catalog')
        ->set('search', 'does-not-exist')
        ->assertSee('No products match that search.')
        ->call('clearFilters')
        ->assertSet('category', 'all')
        ->assertSet('search', '')
        ->assertSee('Everyday Crewneck');
});

it('ignores an unknown category filter', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    Product::factory()->for($category)->create(['name' => 'Everyday Crewneck']);

    Livewire::test('product-catalog')
        ->set('category', 'not-a-category')
        ->assertSee('Everyday Crewneck');
});

it('paginates products one hundred per page', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    Product::factory()->for($category)->count(101)->sequence(
        fn ($sequence) => [
            'name' => 'Product '.($sequence->index + 1),
            'sort_order' => $sequence->index,
        ],
    )->create();

    Livewire::test('product-catalog')
        ->assertSee('Product 1')
        ->assertSee('Product 100')
        ->assertDontSee('Product 101')
        ->assertSee('1–100')
        ->assertSeeHtml('wire:click="nextPage')
        ->call('gotoPage', 2)
        ->assertSee('Product 101')
        ->assertDontSee('Product 100')
        ->assertSee('101–101')
        ->set('search', 'Product')
        ->assertSet('paginators.page', 1)
        ->assertSee('Product 1');
});
