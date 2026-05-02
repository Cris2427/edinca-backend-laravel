<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $fillable = [
        'nombre_completo', 'email', 'telefono',
        'tipo_proyecto', 'descripcion', 'estado', 'archivo_referencia',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
