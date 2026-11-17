<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberWorkoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_workouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('workout_type_id');
            $table->date('assigned_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('workout_type_id')->references('id')->on('workout_types')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_workouts');
    }
}
