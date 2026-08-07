<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulo_lecturas', function (Blueprint $table) {
            $table->dateTime('documento_abierto_at')->nullable()->after('modulo_id');
        });
    }

    public function down(): void
    {
        Schema::table('modulo_lecturas', function (Blueprint $table) {
            $table->dropColumn('documento_abierto_at');
        });
    }
};
