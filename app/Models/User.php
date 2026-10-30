<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // Roles/permisos vía Spatie, mismas tablas que usa xuri-apex.
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'id_card',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function collaborator(): HasOne
    {
        return $this->hasOne(Collaborator::class);
    }

    // A dónde va cada quien apenas entra: quien puede crear módulos ve el
    // panel de pendientes (qué módulos le faltan a quién), el resto de la
    // nómina entra directo a sus módulos asignados.
    public function rutaInicio(): string
    {
        return $this->can('modulos.crear') ? 'dashboard' : 'mis-modulos.index';
    }

    // Quien toma los módulos: todos menos las cuentas marcadas como "solo
    // supervisión" (ver config xr_rrhh.supervision_role).
    public function scopeParticipantes(Builder $query): void
    {
        $query->whereDoesntHave('roles', fn ($q) => $q->where('name', config('xr_rrhh.supervision_role')));
    }

    public function esSoloSupervision(): bool
    {
        return $this->roles->contains('name', config('xr_rrhh.supervision_role'));
    }

    public function modulosCreados(): HasMany
    {
        return $this->hasMany(Modulo::class, 'created_by');
    }

    public function evaluacionIntentos(): HasMany
    {
        return $this->hasMany(EvaluacionIntento::class);
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class);
    }

    public function moduloLecturas(): HasMany
    {
        return $this->hasMany(ModuloLectura::class);
    }

    public function capacitacionAsistencias(): HasMany
    {
        return $this->hasMany(CapacitacionAsistente::class);
    }

    public function moduloAsignaciones(): HasMany
    {
        return $this->hasMany(ModuloAsignacion::class);
    }

    /**
     * Número en formato internacional para links wa.me (sin "+", sin
     * espacios ni guiones). Asume Ecuador si viene en formato local
     * (empieza con 0): "0991234567" -> "593991234567". `phone` es columna
     * de xuri-apex (users.phone), no de xr-rrhh.
     */
    public function telefonoWhatsapp(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $digitos = preg_replace('/\D/', '', $this->phone);

        if (str_starts_with($digitos, '0')) {
            $digitos = '593'.substr($digitos, 1);
        }

        return $digitos ?: null;
    }
}
