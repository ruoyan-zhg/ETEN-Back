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
        Schema::create('usuario_receta', function (Blueprint $table) {
            $table->bigInteger('id_usuario')->unsigned();
            $table->bigInteger('id_receta')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['id_usuario', 'id_receta']);
            $table->foreign('id_usuario')->references('id')->on('usuarios');
            $table->foreign('id_receta')->references('id')->on('recetas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_receta');
    }
};
