<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('email');
            $table->string('telefono')->nullable();
            $table->enum('tipo_proyecto', ['CASA', 'EDIFICIO', 'LOCAL_COMERCIAL', 'AMPLIACION', 'REGULARIZACION']);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['PENDIENTE', 'EN_REVISION', 'APROBADA', 'RECHAZADA', 'COMPLETADA'])->default('PENDIENTE');
            $table->string('archivo_referencia')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
