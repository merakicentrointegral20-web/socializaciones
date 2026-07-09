<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluacion_intentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('modulo_id')->constrained('modulos');
            $table->foreignId('capacitacion_id')->nullable()->constrained('capacitaciones')->nullOnDelete();
            $table->unsignedTinyInteger('score');
            $table->unsignedTinyInteger('total_preguntas');
            $table->boolean('aprobado');
            $table->unsignedInteger('intento_numero')->default(1);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluacion_intentos');
    }
};
