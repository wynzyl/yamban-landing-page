<?php

use App\Models\Category;
use App\Models\Product;

it('shows the landing page with categories and featured products', function () {
    $category = Category::factory()->create([
        'name' => 'Athletic Jersey',
        'slug' => 'athletic-jersey',
        'is_active' => true,
    ]);

    Product::factory()->for($category)->featured()->create([
        'name' => 'Matchday Football Kit',
        'is_active' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Made to order')
        ->assertSee('Athletic Jersey')
        ->assertSee('Matchday Football Kit');
});

it('shows the about page', function () {
    Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    $this->get(route('about'))
        ->assertOk()
        ->assertSee('We make the gear your team remembers.')
        ->assertSee('Hoodies');
});

it('filters the catalog by category from the query string', function () {
    $hoodies = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);
    $jackets = Category::factory()->create(['name' => 'Jackets', 'slug' => 'jackets']);

    Product::factory()->for($hoodies)->create(['name' => 'Everyday Crewneck']);
    Product::factory()->for($jackets)->create(['name' => 'Team Bomber Jacket']);

    $this->get(route('products', ['category' => 'hoodies']))
        ->assertOk()
        ->assertSee('Everyday Crewneck')
        ->assertDontSee('Team Bomber Jacket');
});

it('hides inactive products and categories from the catalog', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

    Product::factory()->for($category)->inactive()->create(['name' => 'Retired Hoodie']);
    Product::factory()->for($category)->create(['name' => 'Current Hoodie']);

    $hiddenCategory = Category::factory()->inactive()->create(['name' => 'Clearance']);

    $this->get(route('products'))
        ->assertOk()
        ->assertSee('Current Hoodie')
        ->assertDontSee('Retired Hoodie')
        ->assertDontSee('Clearance');
});

it('shows the contact page', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Tell us about your project')
        ->assertSee(config('site.email'));
});
