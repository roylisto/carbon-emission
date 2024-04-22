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
        Schema::create('train_routes', function (Blueprint $table) {
            $table->id();
            $table->string('methodology', 20);
            $table->string('origin');
            $table->string('destination');
            $table->string('train_type');
            $table->unsignedBigInteger('emission_id');

            $table->foreign('emission_id')->references('id')->on('emissions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['methodology', 'origin', 'destination', 'train_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('train_routes');
    }
};
