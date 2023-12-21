<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Survey>
 */
class SurveyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        $slug = Str::slug($title);

        return [
            'user_id' => 2,
            'title' => $title,
            'slug' => $slug,
            'status' => fake()->numberBetween(0, 1),
            'description' => fake()->paragraph(),
            'expire_date' => fake()->dateTimeBetween('now', '+1year'),
        ];
    }
}
