<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 5, 500),
            'vendor' => fake()->company(),
            'product_type' => fake()->randomElement(['Apparel', 'Accessories', 'Home & Garden', 'Electronics', 'Books']),
            'tags' => fake()->randomElements(['new', 'sale', 'featured', 'bestseller', 'limited'], fake()->numberBetween(1, 3)),
            'status' => 'draft',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function withShopifyId(): static
    {
        return $this->state(fn (array $attributes) => [
            'shopify_product_id' => (string) fake()->numerify('########'),
        ]);
    }
}
