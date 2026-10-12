<?php

namespace Database\Factories;

use App\Models\FinePayment;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinePayment>
 */
class FinePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loan_id' => Loan::factory(),
            'received_by' => null,
            'amount' => fake()->numberBetween(1, 10000),
            'paid_at' => now(),
            'note' => null,
        ];
    }
}
