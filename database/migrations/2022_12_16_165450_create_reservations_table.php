<?php

use App\Models\User;
use App\Models\Expert;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id()->unique();
            $table->foreignIdFor(User::class,"user");
            $table->foreignIdFor(Expert::class,"expert");
            $table->dateTime("startTime");
            $table->dateTime("endTime");
            $table->integer("rate",false,false)->default(-1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations');
    }
};
