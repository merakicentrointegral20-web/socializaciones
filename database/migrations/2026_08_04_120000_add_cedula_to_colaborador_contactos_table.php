<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Personal de administración no tiene fila en `collaborators` (esa
        // tabla es solo bodega/CEDI), así que hasta ahora su cédula no
        // quedaba guardada en ningún lado tras crear la cuenta. Sin esto,
        // reimportar el mismo CSV de altas creaba una cuenta duplicada por
        // persona en vez de detectar que ya existía.
        Schema::table('colaborador_contactos', function (Blueprint $table) {
            $table->string('cedula', 20)->nullable()->unique()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('colaborador_contactos', function (Blueprint $table) {
            $table->dropColumn('cedula');
        });
    }
};
