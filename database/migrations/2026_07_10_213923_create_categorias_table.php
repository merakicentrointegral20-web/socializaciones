<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // Categorías que hasta ahora vivían como lista fija en código
        // (App\Livewire\Modulos\Index::CATEGORIAS) -- se preservan tal cual
        // para no romper los módulos ya creados con esos valores.
        $ahora = now();
        DB::table('categorias')->insert([
            ['nombre' => 'RRHH', 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Medicina', 'created_at' => $ahora, 'updated_at' => $ahora],
            ['nombre' => 'Seguridad Industrial', 'created_at' => $ahora, 'updated_at' => $ahora],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
