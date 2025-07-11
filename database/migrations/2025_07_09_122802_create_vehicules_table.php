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
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id('num_vil');
            $table->string('marque', 15);
            $table->integer('place');
            $table->string('description', 150);
            $table->decimal('kilometrage', 8, 2);
            $table->enum('disponibilite', ['oui', 'non'])->default('oui');
            $table->integer('prix');
            $table->string('num_chassis', 20);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
