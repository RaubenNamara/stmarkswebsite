<?php

namespace Database\Factories;

use App\Models\CampusVoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampusVoice>
 */
class CampusVoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);
        return [
            'student_name' => fake()->name(),
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'summary' => fake()->paragraph(2),
            'content' => fake()->paragraphs(5, true),
            'featured_image' => null,
            'category' => fake()->randomElement(['Achievement', 'Interview', 'Opinion', 'Profile', 'Story', null]),
            'author' => fake()->name(),
            'featured' => fake()->boolean(10), // 10% chance of being featured
            'status' => fake()->randomElement(['draft', 'published']),
            'views' => fake()->numberBetween(0, 1000),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
