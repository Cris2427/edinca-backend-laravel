<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('tipo', ['CONSTRUCCION_NUEVA', 'AMPLIACION', 'REGULARIZACION', 'REMODELACION']);
            $table->enum('estado', ['PENDIENTE', 'EN_PROCESO', 'EN_EJECUCION', 'FINALIZADO', 'CANCELADO'])->default('PENDIENTE');
            $table->text('descripcion')->nullable();
            $table->decimal('metros_cuadrados', 10, 2)->nullable();
            $table->integer('numero_trabajadores')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin_estimada')->nullable();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
