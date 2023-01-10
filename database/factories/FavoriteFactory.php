<?php

namespace Database\Factories;

use App\Models\Expert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $users_ids = User::pluck('id');
        $experts_ids = Expert::pluck('user_id');
        return [
            "user_id" => fake()->randomElement($users_ids),
            "expert_id" => fake()->randomElement($experts_ids)
        ];
    }
}
