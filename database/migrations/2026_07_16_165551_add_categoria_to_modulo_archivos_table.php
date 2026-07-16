<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulo_archivos', function (Blueprint $table) {
            // Antes había un solo cajón "Materiales". Se separa en dos:
            // 'obligatorio' (lo que ya existía -- revisión obligatoria antes
            // de marcar el módulo como leído) y 'apoyo' (material adicional
            // que el instructor agrega, no bloquea el avance del colaborador).
            $table->string('categoria', 20)->default('obligatorio')->after('modulo_id');
        });
    }

    public function down(): void
    {
        Schema::table('modulo_archivos', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }
};
