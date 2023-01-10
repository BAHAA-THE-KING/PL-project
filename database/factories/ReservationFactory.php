<?php

namespace Database\Factories;

use App\Models\Expert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
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
        $endTime = fake()->dateTimeThisMonth();
        return [
            "user_id" => fake()->randomElement($users_ids),
            "expert_id" => fake()->randomElement($experts_ids),
            "startTime" => fake()->dateTimeThisMonth($endTime),
            "endTime" => $endTime,
            "rate"=>fake()->randomElement([-1,0,1,2,3,4,5])
        ];
    }
}
