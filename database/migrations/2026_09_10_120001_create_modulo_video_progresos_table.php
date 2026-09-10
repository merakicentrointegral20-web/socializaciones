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
        Schema::create('modulo_video_progresos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_video_id')->constrained('modulo_videos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
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
