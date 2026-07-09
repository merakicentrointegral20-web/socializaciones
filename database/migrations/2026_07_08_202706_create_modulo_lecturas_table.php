<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registra cuándo cada persona leyó/revisó el contenido de un
        // módulo -- paso previo e independiente de la capacitación grupal
        // agendada (que tiene su propia fecha/asistencia).
        Schema::create('modulo_lecturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('leido_at')->nullable();
            $table->timestamps();

            $table->unique(['modulo_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo_lecturas');
    }
};
