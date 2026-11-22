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
        // Last valid forecast per location. Failed fetches never overwrite it.
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('location');
            $table->json('data');
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['provider', 'location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_forecasts');
    }
};
