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
        Schema::create('concerner', function (Blueprint $table) {
            $table->unsignedBigInteger('id_reserv');
            $table->unsignedBigInteger('num_vil');
            $table->dateTime('date_reservation');
            $table->primary(['id_reserv', 'num_vil']);

            $table->foreign('id_reserv')->references('id_reserv')->on('reservations')->onDelete('cascade');
            $table->foreign('num_vil')->references('num_vil')->on('vehicules')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concerners');
    }
};
