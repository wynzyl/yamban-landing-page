<?php

use App\Models\Design;
use App\Support\DesignThumbnail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('generates a database preview from a drive image', function () {
    Storage::fake('public');

    $image = imagecreatetruecolor(2400, 1200);
    imagefill($image, 0, 0, imagecolorallocate($image, 200, 50, 50));

    ob_start();
    imagejpeg($image);
    $jpeg = (string) ob_get_clean();

    imagedestroy($image);

    Http::fake([
        'https://drive.usercontent.google.com/download*' => Http::response($jpeg, 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $design = Design::factory()->create([
        'drive_url' => 'https://drive.google.com/file/d/file-1/view',
        'thumbnail_url' => null,
    ]);

    expect(app(DesignThumbnail::class)->generate($design))->toBeTrue();

    $design->refresh();
    $preview = $design->preview;
    $bytes = $preview?->binary();
    $imageInfo = $bytes === null ? false : getimagesizefromstring($bytes);

    expect($design->has_preview)->toBeTrue()
        ->and($design->thumbnail_url)->toBeNull()
        ->and($preview)->not->toBeNull()
        ->and($bytes)->not->toBeNull()
        ->and($imageInfo)->not->toBeFalse()
        ->and($imageInfo['mime'])->toBe('image/webp')
        ->and($imageInfo[0])->toBe(1200)
        ->and($imageInfo[1])->toBe(600)
        ->and(strlen($bytes))->toBeLessThanOrEqual(2 * 1024 * 1024)
        ->and(Storage::disk('public')->exists("designs/{$design->id}.webp"))->toBeFalse()
        ->and($design->previewUrl())->toContain(route('designs.preview', $design));
});

it('does not overwrite an existing preview unless forced', function () {
    Storage::fake('public');
    Http::fake();

    $design = Design::factory()->create([
        'has_preview' => true,
    ]);

    $design->preview()->create([
        'data' => base64_encode('existing-preview'),
        'mime' => 'image/webp',
    ]);

    expect(app(DesignThumbnail::class)->generate($design))->toBeTrue();

    Http::assertNothingSent();

    expect($design->fresh()->preview?->binary())->toBe('existing-preview');
});

it('leaves the preview empty when the drive file is not an image', function () {
    Storage::fake('public');

    Http::fake([
        'https://drive.usercontent.google.com/download*' => Http::response('<html>Not an image</html>', 200, ['Content-Type' => 'text/html']),
    ]);

    $design = Design::factory()->create([
        'drive_url' => 'https://drive.google.com/file/d/file-1/view',
        'thumbnail_url' => null,
    ]);

    expect(app(DesignThumbnail::class)->generate($design))->toBeFalse();

    $design->refresh();

    expect($design->has_preview)->toBeFalse()
        ->and($design->thumbnail_url)->toBeNull()
        ->and($design->preview)->toBeNull();
});

it('imports an existing local preview into the database', function () {
    Storage::fake('public');

    $image = imagecreatetruecolor(10, 10);
    imagefill($image, 0, 0, imagecolorallocate($image, 20, 90, 200));

    ob_start();
    imagewebp($image);
    $webp = (string) ob_get_clean();

    imagedestroy($image);

    $design = Design::factory()->create();

    Storage::disk('public')->put("designs/{$design->id}.webp", $webp);

    expect(app(DesignThumbnail::class)->storeFromLocalFile($design))->toBeTrue();

    $design->refresh();

    expect($design->has_preview)->toBeTrue()
        ->and($design->preview?->binary())->toBe($webp)
        ->and(Storage::disk('public')->exists("designs/{$design->id}.webp"))->toBeFalse();
});

it('serves the stored preview from the database', function () {
    $image = imagecreatetruecolor(12, 8);
    imagefill($image, 0, 0, imagecolorallocate($image, 10, 140, 60));

    ob_start();
    imagewebp($image);
    $webp = (string) ob_get_clean();

    imagedestroy($image);

    $design = Design::factory()->create([
        'has_preview' => true,
    ]);

    $design->preview()->create([
        'data' => base64_encode($webp),
        'mime' => 'image/webp',
    ]);

    $response = $this->get(route('designs.preview', $design));

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/webp')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    expect($response->headers->get('Cache-Control'))
        ->toContain('public')
        ->toContain('max-age=31536000')
        ->toContain('immutable')
        ->and($response->getContent())->toBe($webp);
});

it('returns not found when a design has no stored preview', function () {
    $design = Design::factory()->create();

    $this->get(route('designs.preview', $design))->assertNotFound();
});
