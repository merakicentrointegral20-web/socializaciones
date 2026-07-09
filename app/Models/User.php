<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
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
        ];
    }

    public function collaborator(): HasOne
    {
        return $this->hasOne(Collaborator::class);
    }

    public function contacto(): HasOne
    {
        return $this->hasOne(ColaboradorContacto::class);
    }

    // A dónde va cada quien apenas entra: quien puede crear módulos ve el
    // panel de pendientes (qué módulos le faltan a quién), el resto de la
    // nómina entra directo a sus módulos asignados.
    public function rutaInicio(): string
    {
        return $this->can('modulos.crear') ? 'dashboard' : 'mis-modulos.index';
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
}
