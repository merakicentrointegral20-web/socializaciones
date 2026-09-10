<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            $table->boolean('asignado_a_todos')->default(false)->after('activo');
        });

        // Los módulos que ya existían antes de esta migración eran visibles para
        // todos (no había asignación); se marcan como "asignado a todos" para no
        // ocultarlos de golpe. Los módulos creados de ahora en adelante nacen sin
        // asignar y requieren asignación explícita (ver App\Livewire\Modulos\Asignacion).
        DB::table('modulos')->update(['asignado_a_todos' => true]);
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            $table->dropColumn('asignado_a_todos');
        });
    }
};
