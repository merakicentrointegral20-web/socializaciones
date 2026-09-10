<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asignación puntual de un módulo a colaboradores concretos. Solo aplica
        // cuando modulos.asignado_a_todos es false -- ver
        // App\Models\Modulo::visiblePara().
        Schema::create('modulo_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();

            $table->unique(['modulo_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo_asignaciones');
    }
};
