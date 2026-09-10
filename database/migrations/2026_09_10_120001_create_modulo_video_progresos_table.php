<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registra cuándo cada persona confirmó haber terminado un video --
        // mismo patrón que modulo_lecturas, pero por video en vez de por módulo.
        //
        // Sin llave foránea a propósito -- ver comentario en
        // create_modulo_videos_table.php (falta el privilegio REFERENCES en
        // el usuario de MySQL de producción). Cascada a nivel de aplicación
        // en App\Models\ModuloVideo::booted().
        Schema::create('modulo_video_progresos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modulo_video_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->dateTime('completado_at')->nullable();
            $table->timestamps();

            $table->unique(['modulo_video_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo_video_progresos');
    }
};
