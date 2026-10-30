<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collaborator extends Model
{
    // Solo user_id es escribible desde xr-rrhh (ver database/mysql-app-user.sql).
    // El resto de los datos del colaborador pertenecen a xuri-apex.
    protected $fillable = [
        'user_id',
        'code',
        'first_name',
        'last_name',
        'id_card',
        'function',
        'email',
        'birth_date',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
