<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Videos alojados en un proveedor externo (OneDrive, YouTube, Vimeo --
        // solo se guarda el link embebible), con título/descripción. El orden
        // define la secuencia de desbloqueo -- ver
        // App\Models\Modulo::todosLosVideosCompletados() y
        // App\Livewire\MisModulos\Show::marcarVideoCompletado().
        //
        // Sin llave foránea a propósito: el usuario de MySQL de producción
        // (hosting compartido, Siteground) no tiene privilegio REFERENCES y
        // no se puede conceder desde ahí (confirmado 2026-09-10, ver
        // database/mysql-app-user.sql). El borrado en cascada se maneja a
        // nivel de aplicación en App\Models\Modulo::booted().
        Schema::create('modulo_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modulo_id')->index();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('embed_url', 1000);
            $table->unsignedInteger('orden')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo_videos');
    }
};
