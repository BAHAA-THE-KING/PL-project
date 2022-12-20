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
        return [
            "expert_id" => User::factory(),
            "day" => fake()->randomElement(["SAT", "SUN", "MON", "TUE", "WED", "THI", "FRI"]),
            "start" => fake()->time("H:i", $end),
            "end" => $end
        ];
    }
}
