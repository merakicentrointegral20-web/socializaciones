<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Diapositivas/PDF adjuntos a un módulo. El archivo vive en el disco
        // "local" (storage/app/private, no accesible por URL directa) y se
        // sirve por una ruta autenticada -- ver routes/web.php.
        Schema::create('modulo_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->string('nombre_original');
            $table->string('disk_path');
            $table->string('tipo', 20);
            $table->unsignedInteger('tamano')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo_archivos');
    }
};
