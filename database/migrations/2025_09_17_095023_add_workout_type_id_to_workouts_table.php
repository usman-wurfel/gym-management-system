<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkoutTypeIdToWorkoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->unsignedBigInteger('workout_type_id')->nullable()->after('user_id');
            $table->foreign('workout_type_id')->references('id')->on('workout_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropForeign(['workout_type_id']);
            $table->dropColumn('workout_type_id');
        });
    }
}
