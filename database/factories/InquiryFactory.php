<?php

namespace Database\Factories;

use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('09#########'),
            'company' => fake()->company(),
            'category_id' => null,
            'product_id' => null,
            'quantity' => fake()->numberBetween(10, 100),
            'message' => fake()->paragraph(),
            'status' => 'new',
            'ip_address' => fake()->ipv4(),
        ];
    }

    /**
     * Indicate that the inquiry has already been handled.
     */
    public function handled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'handled',
        ]);
    }
}
