<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ampliar el ENUM tipo para incluir Edificio y Local Comercial
        DB::statement("ALTER TABLE proyectos MODIFY COLUMN tipo ENUM(
            'CONSTRUCCION_NUEVA',
            'AMPLIACION',
            'REGULARIZACION',
            'REMODELACION',
            'EDIFICIO',
            'LOCAL_COMERCIAL'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE proyectos MODIFY COLUMN tipo ENUM(
            'CONSTRUCCION_NUEVA',
            'AMPLIACION',
            'REGULARIZACION',
            'REMODELACION'
        ) NOT NULL");
    }
};
