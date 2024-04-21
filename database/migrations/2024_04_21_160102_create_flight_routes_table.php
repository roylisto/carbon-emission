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
        Schema::create('flight_routes', function (Blueprint $table) {
            $table->id();
            $table->string('methodology', 20)->nullable();
            $table->string('origin', 3);
            $table->string('destination', 3);
            $table->unsignedBigInteger('emission_id');

            $table->foreign('emission_id')->references('id')->on('emissions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['methodology', 'origin', 'destination']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_routes');
    }
};
