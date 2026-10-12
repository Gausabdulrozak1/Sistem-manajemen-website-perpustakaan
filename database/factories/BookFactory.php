<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalStock = fake()->numberBetween(1, 10);

        return [
            'book_code' => fake()->unique()->bothify('BK-####'),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'publication_year' => fake()->numberBetween(1900, now()->year),
            'total_stock' => $totalStock,
            'available_stock' => $totalStock,
            'shelf_location' => fake()->bothify('Rak ?-##'),
        ];
    }
}
