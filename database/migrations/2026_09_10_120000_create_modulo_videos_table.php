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
        Schema::create('modulo_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('embed_url', 1000);
            $table->unsignedInteger('orden')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo_videos');
    }
};
