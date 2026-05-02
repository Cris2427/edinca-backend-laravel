<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Mail\CotizacionEnviada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * CotizacionController
 *
 * Gestiona las cotizaciones económicas asociadas a proyectos.
 *
 * Flujo principal:
 * 1. Admin crea cotización con rango de precios para un proyecto
 * 2. Admin cambia estado a ENVIADA
 * 3. Sistema envía automáticamente correo al cliente del proyecto
 *    con el detalle de la cotización
 */
class CotizacionController extends Controller
{
    /**
     * GET /api/cotizaciones
     *
     * Lista todas las cotizaciones con sus proyectos y clientes asociados.
     * Usa eager loading (with) para evitar el problema N+1 de consultas.
     */
    public function index()
    {
        return response()->json(
            Cotizacion::with(['proyecto.cliente'])
                      ->orderBy('created_at', 'desc')
                      ->get()
        );
    }

    /**
     * GET /api/cotizaciones/proyecto/{proyectoId}
     *
     * Retorna las cotizaciones de un proyecto específico.
     * Útil para mostrar el historial de cotizaciones por proyecto.
     */
    public function porProyecto($proyectoId)
    {
        return response()->json(
            Cotizacion::with(['proyecto.cliente'])
                      ->where('proyecto_id', $proyectoId)
                      ->orderBy('created_at', 'desc')
                      ->get()
        );
    }

    /**
     * POST /api/cotizaciones
     *
     * Crea una nueva cotización vinculada a un proyecto existente.
     * El estado inicial es siempre PENDIENTE (definido en la migración).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'proyecto_id'   => 'required|exists:proyectos,id',
            'precio_minimo' => 'required|numeric|min:0',
            'precio_maximo' => 'required|numeric|min:0',
            'tipo_material' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ]);

        $cotizacion = Cotizacion::create($data);

        // Retorna la cotización con sus relaciones cargadas
        return response()->json(
            Cotizacion::with(['proyecto.cliente'])->find($cotizacion->id),
            201
        );
    }

    /**
     * PATCH /api/cotizaciones/{id}/estado
     *
     * Actualiza el estado de una cotización.
     *
     * Cuando el estado cambia a ENVIADA:
     * - Se busca el email del cliente asociado al proyecto
     * - Se envía un correo HTML con el detalle de la cotización
     * - Si el correo falla, se registra en el log pero NO bloquea la respuesta
     *   (el estado se guarda igual, para no perjudicar al admin)
     */
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:PENDIENTE,ENVIADA,ACEPTADA,RECHAZADA',
        ]);

        $cotizacion = Cotizacion::with(['proyecto.cliente'])->findOrFail($id);
        $cotizacion->update(['estado' => $request->estado]);

        // Envío automático de correo al marcar como ENVIADA
        if ($request->estado === 'ENVIADA') {
            $cliente = $cotizacion->proyecto?->cliente;

            if ($cliente && $cliente->email) {
                try {
                    Mail::to($cliente->email)->send(new CotizacionEnviada($cotizacion));
                } catch (\Exception $e) {
                    // Error silencioso: registra en storage/logs/laravel.log
                    // pero no devuelve error al frontend
                    Log::error('Error enviando correo cotización ID ' . $id . ': ' . $e->getMessage());
                }
            }
        }

        return response()->json(
            Cotizacion::with(['proyecto.cliente'])->find($id)
        );
    }

    /**
     * DELETE /api/cotizaciones/{id}
     *
     * Elimina una cotización. Solo debería permitirse si está en PENDIENTE,
     * pero esa validación de negocio se puede agregar aquí en el futuro.
     */
    public function destroy($id)
    {
        Cotizacion::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
