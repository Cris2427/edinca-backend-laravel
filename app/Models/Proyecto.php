<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    protected $table = 'proyectos';

    protected $fillable = [
        'nombre', 'tipo', 'estado', 'descripcion',
        'metros_cuadrados', 'numero_trabajadores',
        'fecha_inicio', 'fecha_fin_estimada', 'cliente_id',
    ];

    protected $casts = [
        'metros_cuadrados' => 'float',
        'fecha_inicio'     => 'date',
        'fecha_fin_estimada' => 'date',
        'created_at'       => 'datetime',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'proyecto_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'proyecto_id');
    }
}
