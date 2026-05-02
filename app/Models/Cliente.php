<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'nombre', 'email', 'telefono', 'direccion', 'rut',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'cliente_id');
    }
}
