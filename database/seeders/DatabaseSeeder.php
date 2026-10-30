<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for MERAKI.
     */
    public function run(): void
    {
        // 1. Permisos del sistema
        $permisoModulos = Permission::firstOrCreate(['name' => 'modulos.crear', 'guard_name' => 'web']);
        $permisoColaboradores = Permission::firstOrCreate(['name' => config('xr_rrhh.manage_access_permission', 'colaboradores.gestionar'), 'guard_name' => 'web']);

        // 2. Roles del sistema
        $rolInstructor = Role::firstOrCreate(['name' => config('xr_rrhh.instructor_role', 'instructor'), 'guard_name' => 'web']);
        $rolSupervision = Role::firstOrCreate(['name' => config('xr_rrhh.supervision_role', 'supervision'), 'guard_name' => 'web']);

        // Asignar permisos al rol instructor
        $rolInstructor->givePermissionTo([$permisoModulos, $permisoColaboradores]);

        // 3. Categorías iniciales de capacitación para MERAKI
        $categorias = [
            'Inducción General',
            'Seguridad y Salud Ocupacional',
            'Recursos Humanos',
            'Operaciones y Calidad',
            'Procedimientos Internos',
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(['nombre' => $cat]);
        }

        // 4. Usuario Administrador inicial de MERAKI
        $admin = User::firstOrCreate(
            ['email' => 'admin@meraki.com'],
            [
                'name' => 'Administrador MERAKI',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'id_card' => '0999999999',
                'phone' => '0999999999',
                'is_active' => true,
            ]
        );

        $admin->assignRole($rolInstructor);
        $admin->givePermissionTo($permisoColaboradores);

        // 5. Módulo inicial de capacitación si no existe ninguno
        if (\App\Models\Modulo::count() === 0) {
            $cat = Categoria::where('nombre', 'Recursos Humanos')->first() ?? Categoria::first();
            $modulo = \App\Models\Modulo::create([
                'titulo' => 'Módulo 1: Igualdad de Género y No Discriminación',
                'categoria_id' => $cat?->id,
                'categoria' => $cat?->nombre ?? 'Recursos Humanos',
                'tipo_induccion' => 'general',
                'contenido' => 'Capacitación integral sobre igualdad de género, no discriminación y prevención del acoso laboral para todo el personal de MERAKI Centro de Atención Integral.',
                'umbral_aprobacion' => 70,
                'activo' => true,
                'created_by' => $admin->id,
            ]);

            $modulo->videos()->create([
                'titulo' => 'Video 1: Bienvenida al Módulo',
                'descripcion' => 'En este video presentamos el Módulo 1 de Igualdad de Género y No Discriminación. Conocerás tus derechos constitucionales y normativas.',
                'embed_url' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
                'orden' => 1,
                'created_by' => $admin->id,
            ]);
        }
    }
}
