<?php

namespace App\Support;

use App\Models\Design;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DesignThumbnail
{
    /**
     * The largest width or height of a generated preview.
     */
    private const MAX_DIMENSION = 1200;

    /**
     * WebP quality levels tried until the preview is under the size limit.
     *
     * @var array<int, int>
     */
    private const WEBP_QUALITIES = [82, 74, 66, 58, 50, 42, 34, 30];

    /**
     * The largest size allowed for a generated preview, in bytes.
     */
    private const MAX_FILE_SIZE = 2 * 1024 * 1024;

    /**
     * Download and resize the design's Drive file, then store the preview in the database.
     */
    public function generate(Design $design, bool $force = false): bool
    {
        if (! $force && $design->has_preview) {
            return true;
        }

        $fileId = $design->driveFileId();

        if ($fileId === null) {
            return false;
        }

        if (! function_exists('imagewebp')) {
            Log::warning('WebP support is not available in GD.');

            return false;
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'design-preview-');

        if ($temporaryPath === false) {
            return false;
        }

        $previewPath = tempnam(sys_get_temp_dir(), 'design-webp-');

        if ($previewPath === false) {
            @unlink($temporaryPath);

            return false;
        }

        try {
            $response = Http::timeout(120)
                ->withHeaders(['User-Agent' => 'YambanDesignPreview/1.0'])
                ->get('https://drive.usercontent.google.com/download', [
                    'id' => $fileId,
                    'export' => 'view',
                ]);

            if ($response->failed()) {
                Log::warning('Unable to download a design preview.', [
                    'design_id' => $design->id,
                    'status' => $response->status(),
                ]);

                return false;
            }

            file_put_contents($temporaryPath, $response->body());
            unset($response);

            $imageInfo = @getimagesize($temporaryPath);

            if ($imageInfo === false) {
                Log::warning('The downloaded design file is not a supported image.', [
                    'design_id' => $design->id,
                ]);

                return false;
            }

            [$width, $height, $type] = $imageInfo;

            if ($width < 1 || $height < 1) {
                return false;
            }

            $source = $this->createImage($temporaryPath, $type);

            if ($source === null) {
                return false;
            }

            $scale = min(1, self::MAX_DIMENSION / $width, self::MAX_DIMENSION / $height);
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));

            $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
            $preserveAlpha = in_array($type, [IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_AVIF], true);

            if ($preserveAlpha) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
                imagefilledrectangle($thumbnail, 0, 0, $targetWidth, $targetHeight, $transparent);
            } else {
                $white = imagecolorallocate($thumbnail, 255, 255, 255);
                imagefilledrectangle($thumbnail, 0, 0, $targetWidth, $targetHeight, $white);
            }

            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

            $saved = $this->writeWebpWithinLimit($thumbnail, $previewPath);

            imagedestroy($source);
            imagedestroy($thumbnail);

            if (! $saved) {
                Log::warning('Unable to write a generated design preview within the size limit.', [
                    'design_id' => $design->id,
                ]);

                return false;
            }

            $bytes = file_get_contents($previewPath);

            if ($bytes === false) {
                return false;
            }

            return $this->store($design, $bytes);
        } catch (Throwable $exception) {
            Log::warning('Unable to generate a design preview.', [
                'design_id' => $design->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        } finally {
            @unlink($temporaryPath);
            @unlink($previewPath);
        }
    }

    /**
     * Move an existing local WebP preview into the database.
     */
    public function storeFromLocalFile(Design $design): bool
    {
        $path = "designs/{$design->id}.webp";

        if (! Storage::disk('public')->exists($path)) {
            return false;
        }

        $bytes = Storage::disk('public')->get($path);

        if ($bytes === false || $bytes === '') {
            return false;
        }

        $imageInfo = @getimagesizefromstring($bytes);

        if ($imageInfo === false || ($imageInfo['mime'] ?? null) !== 'image/webp') {
            return false;
        }

        return $this->store($design, $bytes);
    }

    /**
     * Store a WebP preview for the design and mark it as available.
     */
    public function store(Design $design, string $bytes, string $mime = 'image/webp'): bool
    {
        if ($bytes === '' || strlen($bytes) > self::MAX_FILE_SIZE) {
            Log::warning('The design preview is empty or larger than the size limit.', [
                'design_id' => $design->id,
                'bytes' => strlen($bytes),
            ]);

            return false;
        }

        try {
            DB::transaction(function () use ($design, $bytes, $mime): void {
                $design->preview()->updateOrCreate([], [
                    'data' => base64_encode($bytes),
                    'mime' => $mime,
                ]);

                $design->forceFill([
                    'has_preview' => true,
                    'thumbnail_url' => null,
                ])->save();
            });
        } catch (Throwable $exception) {
            Log::warning('Unable to store the design preview in the database.', [
                'design_id' => $design->id,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }

        $this->deleteLocalPreview($design);

        return true;
    }

    /**
     * Remove legacy preview files from local storage.
     */
    private function deleteLocalPreview(Design $design): void
    {
        foreach (['webp', 'jpg', 'jpeg', 'png'] as $extension) {
            Storage::disk('public')->delete("designs/{$design->id}.{$extension}");
        }
    }

    /**
     * Write the preview as WebP, lowering the quality until it fits the limit.
     */
    private function writeWebpWithinLimit(\GdImage $image, string $path): bool
    {
        foreach (self::WEBP_QUALITIES as $quality) {
            if (! imagewebp($image, $path, $quality)) {
                continue;
            }

            clearstatcache(true, $path);

            $size = @filesize($path);

            if ($size !== false && $size <= self::MAX_FILE_SIZE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a GD image from the downloaded file.
     */
    private function createImage(string $path, int $type): ?\GdImage
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_AVIF => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : null,
            default => null,
        };
    }
}
