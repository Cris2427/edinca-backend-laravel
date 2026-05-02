<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'titulo', 'mensaje', 'tipo', 'leida',
    ];

    protected $casts = [
        'leida'      => 'boolean',
        'created_at' => 'datetime',
    ];
}
