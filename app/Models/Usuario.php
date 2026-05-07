<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre', 'email', 'password', 'rol', 'activo', 'foto',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'activo'     => 'boolean',
        'created_at' => 'datetime',
    ];

    public $timestamps = true;
}
