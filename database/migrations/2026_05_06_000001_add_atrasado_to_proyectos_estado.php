<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE proyectos MODIFY COLUMN estado ENUM('PENDIENTE','EN_PROCESO','EN_EJECUCION','FINALIZADO','CANCELADO','ATRASADO') NOT NULL DEFAULT 'PENDIENTE'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE proyectos MODIFY COLUMN estado ENUM('PENDIENTE','EN_PROCESO','EN_EJECUCION','FINALIZADO','CANCELADO') NOT NULL DEFAULT 'PENDIENTE'");
    }
};
