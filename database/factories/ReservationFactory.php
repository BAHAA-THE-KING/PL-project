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
        $endTime = fake()->dateTime();
        return [
            "user_id"=>User::factory(),
            "expert_id"=>Expert::factory(),
            "startTime"=>fake()->dateTime($endTime),
            "endTime"=>$endTime,
            "rate"=>fake()->randomElement([-1,0,1,2,3,4,5])
        ];
    }
}
