<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crea el usuario admin si no existe
        if (!Usuario::where('email', 'admin@edinca.cl')->exists()) {
            Usuario::create([
                'nombre'   => 'Administrador EDINCA',
                'email'    => 'admin@edinca.cl',
                'password' => Hash::make('edinca2026'),
                'rol'      => 'ADMIN',
                'activo'   => true,
            ]);
        }
    }
}
