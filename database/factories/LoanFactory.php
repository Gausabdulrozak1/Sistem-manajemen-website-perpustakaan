<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'PJ-'.now()->format('ymd').'-'.strtoupper(Str::random(5));

        return [
            'loan_code' => $code,
            'transaction_code' => $code,
            'member_id' => Member::factory(),
            'book_id' => Book::factory(),
            'borrowed_at' => today(),
            'due_at' => today()->addDays(config('library.loan_period_days', 14)),
            'returned_at' => null,
            'late_days' => 0,
            'fine_amount' => 0,
        ];
    }
}
