<?php

namespace Database\Factories;

use App\Models\Design;
use App\Models\DesignPreview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DesignPreview>
 */
class DesignPreviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $image = imagecreatetruecolor(1, 1);

        ob_start();
        imagewebp($image);
        $data = (string) ob_get_clean();

        imagedestroy($image);

        return [
            'design_id' => Design::factory(),
            'data' => base64_encode($data),
            'mime' => 'image/webp',
        ];
    }
}
