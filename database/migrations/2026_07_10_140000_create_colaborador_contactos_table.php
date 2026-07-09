<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Datos que xuri-apex no tiene dónde guardar (celular, cargo de
        // administración) -- tabla 100% propia de xr-rrhh, nunca se
        // escribe en `collaborators` ni `users` para esto.
        Schema::create('colaborador_contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('telefono', 20)->nullable();
            $table->string('cargo', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colaborador_contactos');
    }
};
