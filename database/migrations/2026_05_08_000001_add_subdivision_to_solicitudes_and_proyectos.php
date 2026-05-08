<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agrega SUBDIVISION al ENUM tipo_proyecto de solicitudes
        DB::statement("ALTER TABLE solicitudes MODIFY COLUMN tipo_proyecto ENUM(
            'CASA','AMPLIACION','REGULARIZACION','EDIFICIO','LOCAL_COMERCIAL','SUBDIVISION'
        ) NOT NULL");

        // Agrega SUBDIVISION al ENUM tipo de proyectos
        DB::statement("ALTER TABLE proyectos MODIFY COLUMN tipo ENUM(
            'CONSTRUCCION_NUEVA','AMPLIACION','REGULARIZACION','REMODELACION','EDIFICIO','LOCAL_COMERCIAL','SUBDIVISION'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE solicitudes MODIFY COLUMN tipo_proyecto ENUM(
            'CASA','AMPLIACION','REGULARIZACION','EDIFICIO','LOCAL_COMERCIAL'
        ) NOT NULL");

        DB::statement("ALTER TABLE proyectos MODIFY COLUMN tipo ENUM(
            'CONSTRUCCION_NUEVA','AMPLIACION','REGULARIZACION','REMODELACION','EDIFICIO','LOCAL_COMERCIAL'
        ) NOT NULL");
    }
};
