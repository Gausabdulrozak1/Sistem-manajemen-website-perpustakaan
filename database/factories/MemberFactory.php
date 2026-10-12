<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_code' => fake()->unique()->bothify('MB-#####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('+62812########'),
            'address' => fake()->address(),
            'joined_at' => fake()->dateTimeBetween('-2 years', 'today')->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
