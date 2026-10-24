<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductImage;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ProductImageStore
{
    /**
     * Largest width or height kept on the stored image, in pixels.
     */
    public const MAX_DIMENSION = 2000;

    /**
     * Lossy WebP quality (0-100).
     */
    public const WEBP_QUALITY = 85;

    /**
     * Convert an uploaded image to WebP, store it, and attach it to the product.
     */
    public function store(Product $product, UploadedFile $file): ProductImage
    {
        $path = $this->storeFile($file);

        return $product->images()->create([
            'path' => $path,
            'sort_order' => (int) $product->images()->max('sort_order') + 1,
        ]);
    }

    /**
     * Convert an uploaded image to WebP and store it on the public disk.
     *
     * @return string The path of the file relative to the public disk.
     */
    public function storeFile(UploadedFile $file): string
    {
        $contents = @file_get_contents($file->getRealPath());

        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if (! $image instanceof GdImage) {
            throw ValidationException::withMessages([
                'photoUploads.*' => __('That file could not be read as an image.'),
            ]);
        }

        try {
            $image = $this->downscale($image);

            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);

            $path = 'products/'.Str::random(40).'.webp';
            $temporary = tempnam(sys_get_temp_dir(), 'webp');

            if ($temporary === false) {
                throw new RuntimeException('The image could not be converted to WebP.');
            }

            try {
                if (! imagewebp($image, $temporary, self::WEBP_QUALITY)) {
                    throw new RuntimeException('The image could not be converted to WebP.');
                }

                Storage::disk(ProductImage::DISK)->put($path, (string) file_get_contents($temporary));
            } finally {
                @unlink($temporary);
            }
        } finally {
            imagedestroy($image);
        }

        return $path;
    }

    /**
     * Shrink the image so neither side exceeds the maximum dimension.
     */
    private function downscale(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, self::MAX_DIMENSION / max($width, $height));

        if ($scale >= 1) {
            return $image;
        }

        $resized = imagecreatetruecolor(
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale)),
        );

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, imagesx($resized), imagesy($resized), $width, $height);

        imagedestroy($image);

        return $resized;
    }
}
