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
        Schema::create('documents', function (Blueprint $table) {
            $table->id('id_document');
            $table->string('acces_img1', 255)->nullable();
            $table->string('acces_img2', 255)->nullable();
            $table->unsignedBigInteger('id_compte');
            $table->timestamps();
            
            $table->foreign('id_compte')->references('id_compte')->on('comptes')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
