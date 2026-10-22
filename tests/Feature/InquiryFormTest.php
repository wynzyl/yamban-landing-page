<?php

use App\Mail\NewInquiry;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
});

it('stores an inquiry and emails the team', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);
    $product = Product::factory()->for($category)->create(['name' => 'Everyday Crewneck']);

    Livewire::test('inquiry-form')
        ->set('name', 'Maria Santos')
        ->set('email', 'maria@example.com')
        ->set('phone', '09171234567')
        ->set('company', 'Santos Trading')
        ->set('categoryId', $category->id)
        ->set('productId', $product->id)
        ->set('quantity', 25)
        ->set('message', 'We need 25 hoodies with our logo before December.')
        ->call('submit')
        ->assertSet('submitted', true)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('inquiries', [
        'name' => 'Maria Santos',
        'email' => 'maria@example.com',
        'product_id' => $product->id,
        'quantity' => 25,
        'status' => 'new',
    ]);

    Mail::assertSent(NewInquiry::class, function (NewInquiry $mail) {
        return $mail->hasTo(config('site.email'))
            && $mail->inquiry->email === 'maria@example.com';
    });
});

it('requires a name, email and message', function () {
    Livewire::test('inquiry-form')
        ->set('name', '')
        ->set('email', '')
        ->set('message', '')
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'message'])
        ->assertSet('submitted', false);

    $this->assertDatabaseCount('inquiries', 0);
    Mail::assertNothingSent();
});

it('rejects an invalid email address', function () {
    Livewire::test('inquiry-form')
        ->set('name', 'Maria Santos')
        ->set('email', 'not-an-email')
        ->set('message', 'Please send a quote for 20 hoodies.')
        ->call('submit')
        ->assertHasErrors(['email']);
});

it('prefills the form from the product query string', function () {
    $category = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);
    $product = Product::factory()->for($category)->create(['slug' => 'everyday-crewneck']);

    Livewire::withQueryParams(['product' => 'everyday-crewneck'])
        ->test('inquiry-form')
        ->assertSet('productId', $product->id)
        ->assertSet('categoryId', $category->id);
});

it('silently discards submissions that fill the honeypot', function () {
    Livewire::test('inquiry-form')
        ->set('name', 'Spam Bot')
        ->set('email', 'bot@example.com')
        ->set('message', 'Buy cheap followers now, guaranteed.')
        ->set('website', 'https://spam.example.com')
        ->call('submit')
        ->assertSet('submitted', true);

    $this->assertDatabaseCount('inquiries', 0);
    Mail::assertNothingSent();
});

it('resets the selection when the category changes', function () {
    $hoodies = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);
    $jackets = Category::factory()->create(['name' => 'Jackets', 'slug' => 'jackets']);
    $product = Product::factory()->for($hoodies)->create();

    Livewire::test('inquiry-form')
        ->set('categoryId', $hoodies->id)
        ->set('productId', $product->id)
        ->set('categoryId', $jackets->id)
        ->assertSet('productId', null);
});
