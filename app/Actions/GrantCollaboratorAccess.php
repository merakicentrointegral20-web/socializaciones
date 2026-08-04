<?php

namespace App\Actions;

use App\Models\Collaborator;
use App\Models\ColaboradorContacto;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Regla de identidad única: nunca crear un usuario para un colaborador que
 * ya tiene collaborator.user_id. Esta acción es el único lugar del proyecto
 * que debe insertar en `users` o enlazar `collaborators.user_id`.
 *
 * También cubre el alta de personas externas a la nómina de bodega/CEDI
 * (ej. médico ocupacional, seguridad industrial) que no tienen ni tendrán
 * fila en `collaborators` -- para ellas se crea la cuenta directamente,
 * con la misma convención de correo/contraseña.
 */
class GrantCollaboratorAccess
{
    /**
     * Valores válidos del ENUM `collaborators.function` en xuri-apex
     * (database/migrations/2026_06_09_172949_fix_collaborator_function_enum.php
     * de ese proyecto). Solo cubre bodega/CEDI -- por diseño de xuri-apex,
     * no hay forma de meter ahí a alguien de otra área.
     */
    public const FUNCIONES_PRODUCCION = [
        'Gestión de bodega',
        'Control de calidad',
        'Despachador',
        'Verificador de mercadería',
        'Sellador',
        'Etiquetado',
    ];

    /**
     * Roles de Xuri Apex de privilegio total -- nunca asignables desde acá,
     * sin importar quién use la pantalla. Un alta masiva de RRHH no es el
     * lugar para otorgar acceso de administrador del sistema; si alguien
     * genuinamente necesita ese nivel, se asigna a mano y con criterio
     * directamente en Xuri Apex.
     */
    public const XURI_APEX_ROLES_EXCLUIDOS = ['admin', 'owner'];

    /**
     * Nombres legibles para mostrar en el selector -- los nombres reales de
     * Spatie son slugs técnicos (admin_cedi, commercial_china...) pensados
     * para código, no para que RRHH elija a ciegas. Un rol que no esté acá
     * (porque se creó después) simplemente se muestra con su nombre técnico
     * tal cual, no se oculta.
     */
    public const XURI_APEX_ROLE_LABELS = [
        'admin_cedi' => 'Administrador de CEDI',
        'commercial' => 'Comercial',
        'commercial_china' => 'Comercial China',
        'control_calidad' => 'Control de calidad',
        'inventory' => 'Inventario',
        'logistics' => 'Logística',
        'supervisor_comercial' => 'Supervisor comercial',
        'warehouse' => 'Bodega',
    ];

    public static function xuriApexRoleLabel(string $role): string
    {
        return self::XURI_APEX_ROLE_LABELS[$role] ?? $role;
    }

    /**
     * Roles operativos de Xuri Apex disponibles para asignar (ej. warehouse,
     * admin_cedi, commercial...). Se leen en vivo de `roles` -- es la misma
     * tabla compartida, así que refleja lo que exista allá sin necesidad de
     * mantener una lista hardcodeada acá. Se excluyen el rol de instructor
     * de xr-rrhh (se gestiona aparte, checkbox "Es instructor") y los roles
     * de privilegio total (ver XURI_APEX_ROLES_EXCLUIDOS).
     *
     * @return array<int, string>
     */
    public static function xuriApexRoles(): array
    {
        return \Spatie\Permission\Models\Role::query()
            ->where('name', '!=', config('xr_rrhh.instructor_role'))
            ->whereNotIn('name', self::XURI_APEX_ROLES_EXCLUIDOS)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * $email/$password explícitos son para el import masivo por archivo de
     * activación (colaboradores.plantilla-activacion): RRHH ya decidió esas
     * credenciales fuera del sistema y solo quiere que se creen tal cual.
     * Sin esos parámetros, se comporta igual que siempre (autogenera ambos).
     *
     * $roles: nombres de roles Spatie a asignar (ya existentes -- esta acción
     * nunca crea roles nuevos). Típicamente el rol de instructor de xr-rrhh
     * y/o un rol operativo de Xuri Apex (despacho, conteo, etc.) -- son cosas
     * independientes, por eso es un array y no un solo valor.
     *
     * @return array{user: User, email: string, password: string}
     */
    public function forCollaborator(Collaborator $collaborator, array $roles = [], ?string $email = null, ?string $password = null): array
    {
        if ($collaborator->user_id) {
            throw new RuntimeException(
                "El colaborador {$collaborator->fullName()} ya tiene una cuenta (regla de identidad única)."
            );
        }

        if ($email && User::where('email', $email)->exists()) {
            throw new RuntimeException("El correo {$email} ya está en uso por otra cuenta.");
        }

        return DB::transaction(function () use ($collaborator, $roles, $email, $password) {
            $created = $this->createUser($collaborator->fullName(), $roles, $email, $password);

            $collaborator->update(['user_id' => $created['user']->id]);

            return $created;
        });
    }

    /**
     * @return array{user: User, email: string, password: string}
     */
    public function forExternalPerson(string $fullName, array $roles = []): array
    {
        return DB::transaction(fn () => $this->createUser($fullName, $roles));
    }

    /**
     * Alta de una persona nueva en la nómina, con dos caminos:
     *  - "produccion" (bodega/CEDI): además de la cuenta, crea la fila en
     *    `collaborators` de xuri-apex, con el mismo formato de código
     *    (CO001...) que usa esa app -- para que la persona quede visible
     *    allá también.
     *  - "administracion" (o cualquier otra área): solo crea la cuenta acá,
     *    `collaborators` no tiene dónde meter estos cargos (su ENUM es
     *    exclusivo de bodega/CEDI).
     *
     * El teléfono y el cargo (o la función de producción, para tenerlo en
     * un solo lugar) se guardan siempre en `colaborador_contactos`, propia
     * de xr-rrhh -- xuri-apex no tiene campo de celular en absoluto.
     *
     * @return array{user: User, email: string, password: string}
     */
    public function forNuevoIngreso(
        string $fullName,
        string $area,
        ?string $cedula,
        ?string $telefono,
        ?string $funcionProduccion,
        ?string $cargoAdministracion,
        array $roles = [],
        ?string $email = null,
        ?string $password = null,
    ): array {
        if ($area === 'produccion') {
            if (! in_array($funcionProduccion, self::FUNCIONES_PRODUCCION, true)) {
                throw new RuntimeException('Selecciona una función de producción válida.');
            }

            if (! $cedula) {
                throw new RuntimeException('La cédula es obligatoria para personal de producción (xuri-apex la exige).');
            }
        }

        if ($email && User::where('email', $email)->exists()) {
            throw new RuntimeException("El correo {$email} ya está en uso por otra cuenta.");
        }

        return DB::transaction(function () use ($fullName, $area, $cedula, $telefono, $funcionProduccion, $cargoAdministracion, $roles, $email, $password) {
            $created = $this->createUser($fullName, $roles, $email, $password);

            if ($area === 'produccion') {
                $this->crearFilaCollaborator($created['user'], $fullName, $funcionProduccion, $cedula, $created['email']);
            }

            ColaboradorContacto::updateOrCreate(
                ['user_id' => $created['user']->id],
                [
                    // Para producción la cédula ya vive en collaborators.id_card,
                    // pero la guardamos acá también -- es la única forma de
                    // detectar un re-import duplicado sin tener que consultar
                    // dos tablas distintas según el área de la persona.
                    'cedula' => $cedula ?: null,
                    'telefono' => $telefono ?: null,
                    'cargo' => $area === 'produccion' ? $funcionProduccion : $cargoAdministracion,
                ]
            );

            return $created;
        });
    }

    private function crearFilaCollaborator(User $user, string $fullName, string $funcion, string $cedula, string $email): Collaborator
    {
        [$firstName, $lastName] = array_pad(explode(' ', trim($fullName), 2), 2, '');

        for ($intento = 1; $intento <= 3; $intento++) {
            try {
                $collaborator = new Collaborator();
                // `collaborators` solo permite escribir user_id desde acá
                // (ver $fillable del modelo) -- este es el único punto del
                // proyecto que a propósito escribe el resto de columnas,
                // por eso forceFill en vez de create().
                $collaborator->forceFill([
                    'code' => $this->siguienteCodigoCollaborator(),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'id_card' => $cedula,
                    'function' => $funcion,
                    'email' => $email,
                    'user_id' => $user->id,
                ]);
                $collaborator->save();

                return $collaborator;
            } catch (QueryException $e) {
                // "code" se calcula leyendo el máximo actual (igual que lo
                // hace xuri-apex) -- bajo carrera concurrente puede chocar
                // con el unique index; reintenta con un código recalculado.
                if ($intento === 3) {
                    throw $e;
                }
            }
        }

        throw new RuntimeException('No se pudo generar un código de colaborador único.');
    }

    /**
     * Misma lógica que app/Livewire/AdminCEDI/CreateCollaborator.php en
     * xuri-apex: código = "CO" + siguiente número de 3 dígitos según el
     * máximo actual.
     */
    private function siguienteCodigoCollaborator(): string
    {
        $ultimo = Collaborator::max('code');
        $numero = $ultimo ? ((int) substr($ultimo, 2)) + 1 : 1;

        return 'CO'.str_pad((string) $numero, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{user: User, email: string, password: string}
     */
    private function createUser(string $fullName, array $roles = [], ?string $email = null, ?string $password = null): array
    {
        [$firstName, $lastName] = array_pad(explode(' ', trim($fullName), 2), 2, '');

        $email ??= $this->generateEmail($firstName, $lastName);
        $password ??= $this->generatePassword();

        $user = User::create([
            'name' => $fullName,
            'email' => $email,
            'password' => Hash::make($password),
            // RRHH ya validó a la persona al crearla acá a mano (contraseña
            // temporal mostrada en pantalla, no por correo) -- no depende
            // de que el SMTP de producción funcione para que pueda entrar.
            'email_verified_at' => now(),
        ]);

        $roles = array_values(array_filter($roles));

        if ($roles) {
            $user->assignRole($roles);
        }

        return ['user' => $user, 'email' => $email, 'password' => $password];
    }

    /**
     * Misma convención que app/Livewire/AdminCEDI/CreateCollaborator.php
     * en xuri-apex (nombreapellido@xurisa.com), para que el correo que
     * generamos aquí sea indistinguible de uno creado allá.
     */
    private function generateEmail(string $firstName, string $lastName): string
    {
        $normalize = function (string $s): string {
            $map = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
                'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n'];

            return preg_replace('/[^a-z]/', '', strtolower(strtr($s, $map)));
        };

        $prefix = $normalize($firstName).$normalize($lastName);
        $email = $prefix.'@xurisa.com';

        if (! User::where('email', $email)->exists()) {
            return $email;
        }

        $i = 2;
        while (User::where('email', $prefix.$i.'@xurisa.com')->exists()) {
            $i++;
        }

        return $prefix.$i.'@xurisa.com';
    }

    private function generatePassword(): string
    {
        $lower = 'abcdefghijkmnpqrstuvwxyz';
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $digits = '23456789';
        $special = '@#$!%*?&';

        $pwd = $lower[random_int(0, strlen($lower) - 1)];
        $pwd .= $upper[random_int(0, strlen($upper) - 1)];
        $pwd .= $digits[random_int(0, strlen($digits) - 1)];
        $pwd .= $special[random_int(0, strlen($special) - 1)];

        $all = $lower.$upper.$digits.$special;
        for ($i = 0; $i < 8; $i++) {
            $pwd .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($pwd);
    }
}
