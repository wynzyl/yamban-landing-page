<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Design;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Design>
 */
class DesignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'category_id' => Category::factory(),
            'name' => $name.' v'.fake()->numberBetween(1, 9).'.png',
            'drive_url' => 'https://drive.google.com/file/d/'.Str::random(28).'/view?usp=sharing',
            'thumbnail_url' => null,
            'has_preview' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate that the design has a stored preview.
     */
    public function withPreview(string $bytes = 'preview', string $mime = 'image/webp'): static
    {
        return $this->afterCreating(function (Design $design) use ($bytes, $mime): void {
            $design->preview()->create([
                'data' => base64_encode($bytes),
                'mime' => $mime,
            ]);

            $design->forceFill(['has_preview' => true])->save();
        });
    }

    /**
     * Indicate that the design is hidden from the design library.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
