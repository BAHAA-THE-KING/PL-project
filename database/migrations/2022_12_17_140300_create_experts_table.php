<?php

use App\Models\Specialty;
use App\Models\User;
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
        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignIdFor(Specialty::class)->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->float('price')->default(50);
            $table->text('description')->default('no description');
            $table->text('address')->default('The free city of Rapture');
            $table->integer('rateSum')->default(0);
            $table->integer('rateCount')->default(0);
            $table->string('specialization',20)->default('general');
            $table->unique(['user_id', 'specialty_id','specialization']);
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
        Schema::dropIfExists('experts');
    }
};
