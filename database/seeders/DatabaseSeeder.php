<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Time;
use App\Models\User;
use App\Models\Expert;
use App\Models\Favorite;
use App\Models\Specialty;
use App\Models\Reservation;
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

        // DB::table('users')->delete();
        // DB::table('experts')->delete();

        User::factory(2)->create();
        $specs=['medical', 'professional', 'adminstrative', 'domestic', 'psychological'];
        foreach ($specs as $spec) {
            Specialty::factory()->create(["specialtyName"=>$spec]);
        }
        Expert::factory(5)->create();
        Favorite::factory(10)->create();
        Reservation::factory(15)->create();
        Time::factory(15)->create();
    }
}
