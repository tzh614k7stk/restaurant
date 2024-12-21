<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('start_date', 10); //yyyy-mm-dd
            $table->string('end_date', 10); //yyyy-mm-dd
            $table->string('start_time', 5); //hh:mm
            $table->string('end_time', 5); //hh:mm
            $table->decimal('duration', 3, 1); //in hours (e.g. 1.5 = 1 hour and 30 minutes)
            $table->integer('seats'); //taking note of the number of seats the table had at the time of making the reservation
            $table->foreignId('table_id')->constrained('tables'); //prevent deletion of tables with reservations
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); //delete reservation when user is deleted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
