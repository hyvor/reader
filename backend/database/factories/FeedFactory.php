<?php

namespace Database\Factories;

use App\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feed>
 */
class FeedFactory extends Factory
{

    public function definition()
    {
        return [
            'url' => $this->faker->url,
            'title' => $this->faker->name,
            'description' => $this->faker->sentence,
            'next_fetch_at' => now()
        ];
    }
}
