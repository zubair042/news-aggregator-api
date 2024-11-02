<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'preferred_sources' => $this->faker->randomElements(['source1', 'source2', 'source3'], 2),
            'preferred_categories' => $this->faker->randomElements(['category1', 'category2', 'category3'], 2),
            'preferred_authors' => $this->faker->randomElements(['author1', 'author2', 'author3'], 2),
        ];
    }
}
