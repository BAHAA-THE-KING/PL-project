<?php

namespace Database\Factories;

use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expert>
 */
class ExpertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $specialtyId = Specialty::all()->pluck('id')->toArray();
        $rateCount = fake()->numberBetween(1,10);
        return [
            'user_id'=>User::factory(),
            'specialty_id'=> fake()->randomElement($specialtyId),
            'price'=>fake()->numberBetween(50,200),
            'description'=>fake()->sentence(6),
            'address'=>fake()->address(),
            'rateSum'=>fake()->numberBetween(0,$rateCount*5),
            'rateCount'=>$rateCount
        ];
    }
}
