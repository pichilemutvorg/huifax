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
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_slot_id')->constrained()->cascadeOnDelete();
            // Date of the regular airing this exception changes.
            $table->date('date');
            // Null cancels the airing; a value moves it there.
            $table->dateTime('rescheduled_to')->nullable();
            $table->timestamps();

            $table->unique(['schedule_slot_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
