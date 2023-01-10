<?php

use App\Models\Expert;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('times', function (Blueprint $table) {
            $table->id("id")->unique();
            $table->unsignedBigInteger("expert_id");
            $table->foreign("expert_id")->references("user_id")->on("experts")->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string("day");
            $table->time("start");
            $table->time("end");
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
        Schema::dropIfExists('times');
    }
};
