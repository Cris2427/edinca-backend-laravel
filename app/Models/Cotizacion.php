<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'proyecto_id', 'precio_minimo', 'precio_maximo',
        'tipo_material', 'observaciones', 'estado',
    ];

    protected $casts = [
        'precio_minimo' => 'decimal:2',
        'precio_maximo' => 'decimal:2',
        'created_at'    => 'datetime',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id')->with('cliente');
    }
}
