<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->onDelete('cascade');
            $table->decimal('precio_minimo', 15, 2);
            $table->decimal('precio_maximo', 15, 2);
            $table->string('tipo_material')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['PENDIENTE', 'ENVIADA', 'ACEPTADA', 'RECHAZADA'])->default('PENDIENTE');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
