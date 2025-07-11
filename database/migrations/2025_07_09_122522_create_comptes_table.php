<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('comptes', function (Blueprint $table) {
            $table->id('id_compte');
            $table->string('email')->unique();
            $table->string('password'); 
            $table->string('username', 15);
            $table->enum('role', ['client', 'admin'])->default('client');
            $table->dateTime('date_connexion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};