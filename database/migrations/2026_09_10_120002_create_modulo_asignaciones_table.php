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
        //
        // Sin llave foránea a propósito -- ver comentario en
        // create_modulo_videos_table.php (falta el privilegio REFERENCES en
        // el usuario de MySQL de producción). Cascada a nivel de aplicación
        // en App\Models\Modulo::booted().
        Schema::create('modulo_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modulo_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('assigned_by');
            $table->timestamps();

            $table->unique(['modulo_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo_asignaciones');
    }
};
