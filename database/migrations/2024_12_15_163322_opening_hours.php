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
            $table->string('day', 16)->unique(); //monday-sunday for regular opening hours, or date for custom opening hours
            $table->time('open')->nullable(); //null to be used on closed days
            $table->time('close')->nullable(); //null to be used on closed days
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
