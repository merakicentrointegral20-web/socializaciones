<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacitacion_asistentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capacitacion_id')->constrained('capacitaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('asistio')->default(false);
            $table->dateTime('asistio_at')->nullable();
            $table->timestamps();

            $table->unique(['capacitacion_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacitacion_asistentes');
    }
};
