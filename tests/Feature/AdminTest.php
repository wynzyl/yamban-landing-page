<?php

use App\Models\Category;
use App\Models\Design;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('seeds a single admin with the default password', function () {
    User::factory()->admin()->create();

    $this->seed(AdminSeeder::class);
    $this->seed(AdminSeeder::class);

    $admin = User::query()->where('email', User::ADMIN_EMAIL)->firstOrFail();

    expect($admin->isAdmin())->toBeTrue()
        ->and(Hash::check('admin123', $admin->password))->toBeTrue()
        ->and(User::query()->where('is_admin', true)->count())->toBe(1);
});

it('lets the seeded admin log in', function () {
    $this->seed(AdminSeeder::class);

    $this->post(route('login.store'), ['email' => User::ADMIN_EMAIL, 'password' => 'admin123'])
        ->assertRedirect();

    $this->assertAuthenticated();
});

it('keeps guests and regular users out of the admin area', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())->get(route($route))->assertForbidden();
})->with(['admin.categories', 'admin.products', 'admin.designs']);

it('lets the admin open the admin pages', function (string $route) {
    $this->actingAs(User::factory()->admin()->create())->get(route($route))->assertOk();
})->with(['admin.categories', 'admin.products', 'admin.designs']);

it('creates, edits and deletes categories', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test('pages::admin.categories')
        ->set('name', 'Team Caps')
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::query()->where('slug', 'team-caps')->firstOrFail();

    Livewire::test('pages::admin.categories')
        ->call('edit', $category->id)
        ->set('name', 'Caps')
        ->call('save')
        ->call('toggleActive', $category->id)
        ->assertHasNoErrors();

    expect($category->refresh())->name->toBe('Caps')->is_active->toBeFalse();

    Livewire::test('pages::admin.categories')->call('delete', $category->id);

    expect(Category::query()->count())->toBe(0);
});

it('validates category slugs are unique', function () {
    $this->actingAs(User::factory()->admin()->create());
    Category::factory()->create(['slug' => 'hoodies']);

    Livewire::test('pages::admin.categories')
        ->set('name', 'Hoodies')
        ->call('save')
        ->assertHasErrors('slug');
});

it('creates, edits and deletes products', function () {
    $this->actingAs(User::factory()->admin()->create());
    $category = Category::factory()->create();

    Livewire::test('pages::admin.products')
        ->set('category_id', $category->id)
        ->set('name', 'Team Bomber')
        ->set('features', "Water resistant\n\nRibbed cuffs")
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::query()->where('slug', 'team-bomber')->firstOrFail();
    expect($product->features)->toBe(['Water resistant', 'Ribbed cuffs']);

    Livewire::test('pages::admin.products')
        ->call('edit', $product->id)
        ->set('is_featured', true)
        ->call('save')
        ->call('toggleActive', $product->id);

    expect($product->refresh())->is_featured->toBeTrue()->is_active->toBeFalse();

    Livewire::test('pages::admin.products')->call('delete', $product->id);

    expect(Product::query()->count())->toBe(0);
});

it('requires an existing category for a product', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test('pages::admin.products')
        ->set('name', 'Orphan')
        ->call('save')
        ->assertHasErrors('category_id');
});

it('converts uploaded product photos to webp and records them', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $category = Category::factory()->create();

    Livewire::test('pages::admin.products')
        ->set('category_id', $category->id)
        ->set('name', 'Photo Kit')
        ->set('photoUploads', [UploadedFile::fake()->image('kit.jpg', 3000, 1500)])
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::query()->where('slug', 'photo-kit')->firstOrFail();
    $photo = $product->images()->firstOrFail();

    expect($photo->path)->toEndWith('.webp');

    Storage::disk('public')->assertExists($photo->path);

    $size = getimagesizefromstring(Storage::disk('public')->get($photo->path));

    expect($size['mime'])->toBe('image/webp')
        ->and(max($size[0], $size[1]))->toBe(2000)
        ->and($product->imageUrl())->toBe(Storage::disk('public')->url($photo->path));
});

it('stores photos immediately when uploading to an existing product', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $product = Product::factory()->create();

    Livewire::test('pages::admin.products')
        ->call('edit', $product->id)
        ->set('photoUploads', [UploadedFile::fake()->image('front.png', 800, 800)])
        ->assertHasNoErrors()
        ->assertSet('photoUploads', []);

    $photo = $product->images()->firstOrFail();

    expect($photo->path)->toEndWith('.webp')
        ->and($photo->product_id)->toBe($product->id);

    Storage::disk('public')->assertExists($photo->path);
});

it('rejects files that are not images', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test('pages::admin.products')
        ->set('category_id', Category::factory()->create()->id)
        ->set('name', 'Bad Upload')
        ->set('photoUploads', [UploadedFile::fake()->create('contract.pdf', 20, 'application/pdf')])
        ->call('save')
        ->assertHasErrors('photoUploads.0');

    expect(ProductImage::query()->count())->toBe(0);
});

it('deletes a single photo and its file', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $product = Product::factory()->create();
    $path = 'products/deletable.webp';

    Storage::disk('public')->put($path, 'fake webp contents');

    $photo = ProductImage::factory()->create(['product_id' => $product->id, 'path' => $path]);

    Livewire::test('pages::admin.products')
        ->call('edit', $product->id)
        ->call('deletePhoto', $photo->id);

    Storage::disk('public')->assertMissing($path);
    expect(ProductImage::query()->count())->toBe(0);
});

it('removes photo files when a product is deleted', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
    $product = Product::factory()->create();
    $path = 'products/cleanup.webp';

    Storage::disk('public')->put($path, 'fake webp contents');
    ProductImage::factory()->create(['product_id' => $product->id, 'path' => $path]);

    Livewire::test('pages::admin.products')->call('delete', $product->id);

    Storage::disk('public')->assertMissing($path);
    expect(ProductImage::query()->count())->toBe(0);
});

it('creates, edits and deletes designs', function () {
    $this->actingAs(User::factory()->admin()->create());
    $category = Category::factory()->create();

    Livewire::test('pages::admin.designs')
        ->set('category_id', $category->id)
        ->set('name', 'Basketball Jersey - Home.png')
        ->set('drive_url', 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view?usp=sharing')
        ->call('save')
        ->assertHasNoErrors();

    $design = Design::query()->where('name', 'Basketball Jersey - Home.png')->firstOrFail();

    expect($design->category_id)->toBe($category->id)
        ->and($design->driveFileId())->toBe('1AbCdEfGhIjKlMnOp');

    Livewire::test('pages::admin.designs')
        ->call('edit', $design->id)
        ->set('name', 'Basketball Jersey - Away.png')
        ->call('save')
        ->call('toggleActive', $design->id)
        ->assertHasNoErrors();

    expect($design->refresh())->name->toBe('Basketball Jersey - Away.png')->is_active->toBeFalse();

    Livewire::test('pages::admin.designs')->call('delete', $design->id);

    expect(Design::query()->count())->toBe(0);
});

it('searches admin designs by filename and category', function () {
    $this->actingAs(User::factory()->admin()->create());

    $hoodies = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);
    $jerseys = Category::factory()->create(['name' => 'Athletic Jersey', 'slug' => 'athletic-jersey']);

    Design::factory()->for($hoodies)->create(['name' => 'Pullover Hoodie - Front.png']);
    Design::factory()->for($jerseys)->create(['name' => 'Team Blazer - Away.png']);

    Livewire::test('pages::admin.designs')
        ->set('search', 'Pullover')
        ->assertSee('Pullover Hoodie - Front.png')
        ->assertDontSee('Team Blazer - Away.png')
        ->set('search', 'athletic')
        ->assertSee('Team Blazer - Away.png')
        ->assertDontSee('Pullover Hoodie - Front.png');
});

it('normalizes a design drive link without a scheme', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test('pages::admin.designs')
        ->set('category_id', Category::factory()->create()->id)
        ->set('name', 'Hoodie Mockup.png')
        ->set('drive_url', 'drive.google.com/file/d/abc123/view')
        ->call('save')
        ->assertHasNoErrors();

    expect(Design::query()->firstOrFail()->drive_url)->toBe('https://drive.google.com/file/d/abc123/view');
});

it('requires a google drive link and a category for a design', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test('pages::admin.designs')
        ->set('name', 'Orphan Design.png')
        ->set('drive_url', 'https://example.com/design.png')
        ->call('save')
        ->assertHasErrors(['category_id', 'drive_url']);
});
