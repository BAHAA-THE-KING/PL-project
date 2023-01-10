<?php

namespace Database\Factories;

use App\Models\Expert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Time>
 */
class TimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $end = fake()->time("H:i");
        $ids = Expert::pluck("user_id");
        return [
            "expert_id" => fake()->randomElement($ids),
            "day" => fake()->randomElement(["Saturday","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday"]),
            "start" => fake()->time("H:i", $end),
            "end" => $end
        ];
    }
}
