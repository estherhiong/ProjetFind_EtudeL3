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
            $table->id('id_reserv');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('moyen_paie', 15);
            $table->decimal('montant', 8, 2);
            $table->integer('num_contrat');
            $table->string('statut', 15);
            $table->string('moyen_reserv', 20);
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
