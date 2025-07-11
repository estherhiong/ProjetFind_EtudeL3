<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id('id_images');
            $table->unsignedBigInteger('num_vil');
            $table->string('path'); // Chemin d'accès de l'image
            $table->integer('order')->default(0); // Optionnel: pour ordonner les images
            $table->timestamps();

            $table->foreign('num_vil')->references('num_vil')->on('vehicules')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
