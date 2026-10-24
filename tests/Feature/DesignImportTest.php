<?php

use App\Jobs\GenerateDesignThumbnails;
use App\Models\Category;
use App\Models\Design;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    config(['services.google.drive_api_key' => 'test-key']);
    Queue::fake();

    $this->category = Category::factory()->create([
        'name' => 'Athletic Jersey',
        'slug' => 'athletic-jersey',
    ]);
});

it('saves each pasted drive link using the drive filename', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files/file-1*' => Http::response([
            'name' => 'Basketball Jersey - Home.png',
        ]),
        'https://www.googleapis.com/drive/v3/files/file-2*' => Http::response([
            'name' => 'Basketball Jersey - Away.png',
        ]),
    ]);

    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', "https://drive.google.com/file/d/file-1/view?usp=sharing\nhttps://drive.google.com/open?id=file-2")
        ->call('bulkImport')
        ->assertHasNoErrors();

    expect(Design::query()->count())->toBe(2)
        ->and(Design::query()->where('drive_url', 'like', '%file-1%')->firstOrFail()->name)->toBe('Basketball Jersey - Home.png')
        ->and(Design::query()->where('drive_url', 'like', '%file-2%')->firstOrFail()->name)->toBe('Basketball Jersey - Away.png')
        ->and(Design::query()->firstOrFail()->category_id)->toBe($this->category->id);

    Queue::assertPushed(GenerateDesignThumbnails::class);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'file-1')
        && str_contains($request->url(), 'test-key'));
});

it('uses a custom name when one is given before the link', function () {
    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', 'Home Kit.png | https://drive.google.com/file/d/file-9/view')
        ->call('bulkImport')
        ->assertHasNoErrors();

    expect(Design::query()->firstOrFail())
        ->name->toBe('Home Kit.png')
        ->drive_url->toBe('https://drive.google.com/file/d/file-9/view');

    Http::assertNothingSent();
});

it('accepts a drive link without a scheme', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files/file-7*' => Http::response([
            'name' => 'Scheme Link.png',
        ]),
    ]);

    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', 'drive.google.com/file/d/file-7/view')
        ->call('bulkImport')
        ->assertHasNoErrors();

    expect(Design::query()->firstOrFail())->name->toBe('Scheme Link.png');
});

it('imports every file in a drive folder including subfolders', function () {
    Http::fake(function ($request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);
        $q = $query['q'] ?? '';

        if (str_contains($q, "'root-folder' in parents")) {
            return Http::response([
                'files' => [
                    ['id' => 'sub-folder', 'name' => 'Subfolder', 'mimeType' => 'application/vnd.google-apps.folder'],
                    ['id' => 'file-1', 'name' => 'Home.png', 'mimeType' => 'image/png', 'webViewLink' => 'https://drive.google.com/file/d/file-1/view'],
                ],
            ]);
        }

        if (str_contains($q, "'sub-folder' in parents")) {
            return Http::response([
                'files' => [
                    ['id' => 'file-2', 'name' => 'Away.png', 'mimeType' => 'image/png', 'webViewLink' => 'https://drive.google.com/file/d/file-2/view'],
                ],
            ]);
        }

        return Http::response(['files' => []]);
    });

    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', 'https://drive.google.com/drive/folders/root-folder?usp=sharing')
        ->call('bulkImport')
        ->assertHasNoErrors();

    expect(Design::query()->count())->toBe(2)
        ->and(Design::query()->where('name', 'Home.png')->firstOrFail()->drive_url)->toBe('https://drive.google.com/file/d/file-1/view')
        ->and(Design::query()->where('name', 'Away.png')->firstOrFail()->category_id)->toBe($this->category->id);
});

it('only imports image files from a drive folder', function () {
    Http::fake(function ($request) {
        parse_str(parse_url($request->url(), PHP_URL_QUERY) ?: '', $query);
        $q = $query['q'] ?? '';

        if (str_contains($q, "'root-folder' in parents")) {
            return Http::response([
                'files' => [
                    ['id' => 'pdf-1', 'name' => 'Spec sheet.pdf', 'mimeType' => 'application/pdf'],
                    ['id' => 'doc-1', 'name' => 'Design brief', 'mimeType' => 'application/vnd.google-apps.document'],
                    ['id' => 'file-1', 'name' => 'Home.png', 'mimeType' => 'image/png', 'webViewLink' => 'https://drive.google.com/file/d/file-1/view'],
                ],
            ]);
        }

        return Http::response(['files' => []]);
    });

    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', 'https://drive.google.com/drive/folders/root-folder')
        ->call('bulkImport')
        ->assertHasNoErrors();

    expect(Design::query()->count())->toBe(1)
        ->and(Design::query()->firstOrFail()->name)->toBe('Home.png');
});

it('skips links that already exist and duplicates in the pasted list', function () {
    Design::factory()->for($this->category)->create([
        'name' => 'Existing Design.png',
        'drive_url' => 'https://drive.google.com/file/d/file-1/view',
    ]);

    Http::fake([
        'https://www.googleapis.com/drive/v3/files/file-2*' => Http::response([
            'name' => 'New Design.png',
        ]),
    ]);

    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', "https://drive.google.com/file/d/file-1/view\nhttps://drive.google.com/file/d/file-2/view\nhttps://drive.google.com/file/d/file-2/view")
        ->call('bulkImport')
        ->assertHasNoErrors();

    expect(Design::query()->count())->toBe(2)
        ->and(Design::query()->where('name', 'New Design.png')->exists())->toBeTrue();
});

it('falls back to the file id when drive metadata is unavailable', function () {
    config(['services.google.drive_api_key' => null]);

    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', 'https://drive.google.com/file/d/file-abc/view')
        ->call('bulkImport')
        ->assertHasNoErrors();

    expect(Design::query()->firstOrFail())->name->toBe('Design file-abc');
});

it('rejects a batch without any drive folder or file links', function () {
    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', "not a link\nhttps://example.com/nothing\nhttps://example.com/?id=file-1")
        ->call('bulkImport')
        ->assertHasErrors('bulk_links');

    expect(Design::query()->count())->toBe(0);
});

it('reports a folder that cannot be read', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files?*' => Http::response([
            'error' => ['errors' => [['reason' => 'notFound']]],
        ], 404),
    ]);

    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', 'https://drive.google.com/drive/folders/missing-folder')
        ->call('bulkImport')
        ->assertHasErrors('bulk_links');

    expect(Design::query()->count())->toBe(0);
});

it('queues preview generation for designs without previews', function () {
    Design::factory()->for($this->category)->create([
        'thumbnail_url' => null,
    ]);

    Livewire::test('pages::admin.designs')
        ->call('generatePreviews')
        ->assertHasNoErrors();

    Queue::assertPushed(GenerateDesignThumbnails::class);
});

it('still imports valid links when some lines are invalid', function () {
    Http::fake([
        'https://www.googleapis.com/drive/v3/files/file-3*' => Http::response([
            'name' => 'Team Hoodie.png',
        ]),
    ]);

    Livewire::test('pages::admin.designs')
        ->set('bulk_category_id', $this->category->id)
        ->set('bulk_links', "not a link\nhttps://drive.google.com/file/d/file-3/view")
        ->call('bulkImport')
        ->assertHasNoErrors();

    expect(Design::query()->count())->toBe(1)
        ->and(Design::query()->firstOrFail()->name)->toBe('Team Hoodie.png');
});
