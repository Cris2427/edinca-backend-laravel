<?php

/**
 * routes/api.php — Rutas de la API REST de EDINCA
 *
 * Todas las rutas tienen el prefijo /api/ automáticamente (configurado en bootstrap/app.php).
 *
 * Estructura:
 * - Rutas públicas: accesibles sin token (login, formulario landing)
 * - Rutas protegidas: requieren header "Authorization: Bearer {token}"
 *   El token lo genera Sanctum al hacer login y el frontend lo guarda en localStorage.
 */

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\SolicitudController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas (sin autenticación)
|--------------------------------------------------------------------------
|
| Rate limiting aplicado para proteger contra fuerza bruta y spam:
|
| throttle:5,1  → máximo 5 intentos por minuto por IP (login)
|   Si se superan, Laravel devuelve 429 Too Many Requests automáticamente.
|   Ideal para bloquear ataques de fuerza bruta sobre credenciales.
|
| throttle:20,1 → máximo 20 envíos por minuto por IP (formulario público)
|   Evita que bots llenen la BD de solicitudes falsas.
|
| Estas reglas son nativas de Laravel (no requieren paquetes externos)
| y funcionan igual en local, staging y producción (HostGator).
*/

// Login del panel admin — 5 intentos por minuto por IP
Route::middleware('throttle:5,1')->post('/auth/login', [AuthController::class, 'login']);

// Formulario de contacto del landing — 20 envíos por minuto por IP
Route::middleware('throttle:20,1')->post('/solicitudes', [SolicitudController::class, 'store']);

// Verificación de que la API está activa (útil para monitoreo)
Route::get('/health', fn() => response()->json(['status' => 'ok', 'app' => 'EDINCA API']));

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren token Sanctum)
|--------------------------------------------------------------------------
| El middleware 'auth:sanctum' verifica automáticamente el token
| en el header Authorization: Bearer {token}
| Si el token es inválido o no existe → 401 Unauthorized
*/
Route::middleware('auth:sanctum')->group(function () {

    // ── Autenticación ──────────────────────────────────────────────
    Route::post('/auth/logout',           [AuthController::class, 'logout']);
    Route::post('/auth/cambiar-password', [AuthController::class, 'cambiarPassword']);
    Route::post('/auth/foto',             [AuthController::class, 'subirFoto']);
    Route::delete('/auth/foto',           [AuthController::class, 'eliminarFoto']);

    // ── Solicitudes de cotización ──────────────────────────────────
    Route::get('/solicitudes',                 [SolicitudController::class, 'index']);
    Route::get('/solicitudes/{id}',            [SolicitudController::class, 'show']);
    Route::get('/solicitudes/estado/{estado}', [SolicitudController::class, 'porEstado']);
    Route::patch('/solicitudes/{id}/estado',   [SolicitudController::class, 'actualizarEstado']);
    Route::delete('/solicitudes/{id}',         [SolicitudController::class, 'destroy']);

    // ── Clientes ───────────────────────────────────────────────────
    Route::get('/clientes',         [ClienteController::class, 'index']);
    Route::get('/clientes/{id}',    [ClienteController::class, 'show']);
    Route::post('/clientes',        [ClienteController::class, 'store']);
    Route::put('/clientes/{id}',    [ClienteController::class, 'update']);
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);

    // ── Proyectos ──────────────────────────────────────────────────
    Route::get('/proyectos',               [ProyectoController::class, 'index']);
    Route::get('/proyectos/{id}',          [ProyectoController::class, 'show']);
    Route::post('/proyectos',              [ProyectoController::class, 'store']);
    Route::patch('/proyectos/{id}/estado', [ProyectoController::class, 'actualizarEstado']);
    Route::delete('/proyectos/{id}',       [ProyectoController::class, 'destroy']);

    // ── Cotizaciones ───────────────────────────────────────────────
    Route::get('/cotizaciones',                       [CotizacionController::class, 'index']);
    Route::get('/cotizaciones/proyecto/{proyectoId}', [CotizacionController::class, 'porProyecto']);
    Route::post('/cotizaciones',                      [CotizacionController::class, 'store']);
    Route::patch('/cotizaciones/{id}/estado',         [CotizacionController::class, 'actualizarEstado']);
    Route::delete('/cotizaciones/{id}',               [CotizacionController::class, 'destroy']);

    // ── Documentos PDF ─────────────────────────────────────────────
    Route::get('/documentos',                        [DocumentoController::class, 'index']);
    Route::get('/documentos/proyecto/{proyectoId}',  [DocumentoController::class, 'porProyecto']);
    Route::post('/documentos/upload',                [DocumentoController::class, 'upload']);
    Route::delete('/documentos/{id}',                [DocumentoController::class, 'destroy']);

    // ── Notificaciones del panel ───────────────────────────────────
    Route::get('/notificaciones',              [NotificacionController::class, 'index']);
    Route::get('/notificaciones/no-leidas',    [NotificacionController::class, 'noLeidas']);
    Route::get('/notificaciones/conteo',       [NotificacionController::class, 'conteo']);
    Route::patch('/notificaciones/{id}/leer',  [NotificacionController::class, 'marcarLeida']);
    Route::patch('/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodasLeidas']);
});
