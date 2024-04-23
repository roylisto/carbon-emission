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
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('methodology', 20);
            $table->string('country')->nullable();
            $table->unsignedInteger('stars')->nullable();
            $table->boolean('hcmi_member')->default(false);
            $table->string('room_type')->nullable();
            $table->unsignedBigInteger('emission_id');
            $table->timestamps();

            $table->foreign('emission_id')->references('id')->on('emissions')->onDelete('cascade');
            $table->unique(['methodology', 'country', 'stars', 'hcmi_member', 'room_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
