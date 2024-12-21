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
        Schema::create('opening_hours', function (Blueprint $table) {
            $table->id();
            $table->string('day', 10)->unique(); //monday-sunday for regular opening hours, or yyyy-mm-dd for custom opening hours
            $table->string('open', 5)->nullable(); //hh:mm, null to be used on closed days
            $table->string('close', 5)->nullable(); //hh:mm, null to be used on closed days
            $table->boolean('close_on_next_day')->default(false); //if true, the close time is on the next day (e.g. 16:00 mon -> 02:00 tue)
            $table->boolean('closed')->default(false); //if true, ignore open and close times
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opening_hours');
    }
};
