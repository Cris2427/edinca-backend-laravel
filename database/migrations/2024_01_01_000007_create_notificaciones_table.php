<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('mensaje');
            $table->enum('tipo', ['SOLICITUD_NUEVA', 'PROYECTO_ACTUALIZADO', 'SISTEMA'])->default('SISTEMA');
            $table->boolean('leida')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
