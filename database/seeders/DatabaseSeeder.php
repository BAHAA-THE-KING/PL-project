<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Expert;
use App\Models\Favorite;
use App\Models\Reservation;
use App\Models\Specialty;
use App\Models\User;
use App\Models\Time;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //User::factory(5)->create();
        // foreach(User::all() as $user){
        //     $user->createToken('user');
        // }
        Specialty::factory(3)->create();
        Expert::factory()->create();
        Favorite::factory(10)->create();
        Reservation::factory(15)->create();
        Time::factory(15)->create();
    }
}
