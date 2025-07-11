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
        Schema::create('effectuer', function (Blueprint $table) {
            $table->unsignedBigInteger('id_compte');
            $table->unsignedBigInteger('id_reserv');
            $table->dateTime('date_reserv2');
            $table->primary(['id_compte', 'id_reserv']);

            $table->foreign('id_compte')->references('id_compte')->on('comptes')->onDelete('cascade');
            $table->foreign('id_reserv')->references('id_reserv')->on('reservations')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('effectuers');
    }
};
