<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Antes toda inducción era presencial (así lo dice el certificado);
        // ahora también se hace virtual (lectura + videos, sin sesión en vivo).
        // Se guarda por módulo para que el certificado redacte el párrafo
        // correcto -- ver App\Models\Certificado::generarPdf().
        Schema::table('modulos', function (Blueprint $table) {
            $table->string('tipo_induccion', 20)->default('presencial')->after('categoria');
        });
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            $table->dropColumn('tipo_induccion');
        });
    }
};
