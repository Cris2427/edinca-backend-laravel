<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use App\Models\Notificacion;
use Illuminate\Http\Request;

/**
 * SolicitudController
 *
 * Gestiona las solicitudes de cotización enviadas desde el formulario
 * público del sitio web landing (edinca.cl).
 *
 * El método store() es público (sin autenticación) para que cualquier
 * visitante pueda enviar una solicitud. El resto de métodos requieren
 * token de admin (definido en routes/api.php).
 */
class SolicitudController extends Controller
{
    /**
     * GET /api/solicitudes
     *
     * Retorna todas las solicitudes ordenadas por más reciente.
     * Usada por el panel admin para listar y gestionar solicitudes.
     */
    public function index()
    {
        return response()->json(
            Solicitud::orderBy('created_at', 'desc')->get()
        );
    }

    /**
     * GET /api/solicitudes/{id}
     *
     * Retorna una solicitud específica por su ID.
     * findOrFail lanza 404 automáticamente si no existe.
     */
    public function show($id)
    {
        $solicitud = Solicitud::findOrFail($id);
        return response()->json($solicitud);
    }

    /**
     * GET /api/solicitudes/estado/{estado}
     *
     * Filtra solicitudes por estado (PENDIENTE, EN_REVISION, etc.).
     * Útil para el panel admin cuando quiere ver solo las pendientes.
     */
    public function porEstado($estado)
    {
        return response()->json(
            Solicitud::where('estado', $estado)
                     ->orderBy('created_at', 'desc')
                     ->get()
        );
    }

    /**
     * POST /api/solicitudes  ← RUTA PÚBLICA
     *
     * Crea una nueva solicitud desde el formulario del landing.
     * No requiere autenticación.
     *
     * Al guardar, crea automáticamente una notificación para el admin,
     * que aparece en tiempo real en el panel (polling cada 10 segundos).
     */
    public function store(Request $request)
    {
        // Validación de campos del formulario
        $data = $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'email'           => 'nullable|email',
            'telefono'        => 'nullable|string|max:20',
            'tipo_proyecto'   => 'required|in:CASA,EDIFICIO,LOCAL_COMERCIAL,AMPLIACION,REGULARIZACION',
            'descripcion'     => 'nullable|string',
        ]);

        $solicitud = Solicitud::create($data);

        // Genera notificación automática visible en el panel admin
        Notificacion::create([
            'titulo'  => 'Nueva solicitud recibida',
            'mensaje' => $solicitud->nombre_completo . ' solicitó una ' .
                         strtolower(str_replace('_', ' ', $solicitud->tipo_proyecto)),
            'tipo'    => 'SOLICITUD_NUEVA',
            'leida'   => false,
        ]);

        return response()->json($solicitud, 201);
    }

    /**
     * PATCH /api/solicitudes/{id}/estado
     *
     * Actualiza el estado de una solicitud (ej: PENDIENTE → APROBADA).
     * Cuando se aprueba, el frontend muestra el modal para crear cliente.
     */
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:PENDIENTE,EN_REVISION,APROBADA,RECHAZADA',
        ]);

        $solicitud = Solicitud::findOrFail($id);
        $solicitud->update(['estado' => $request->estado]);

        return response()->json($solicitud);
    }

    /**
     * DELETE /api/solicitudes/{id}
     *
     * Elimina una solicitud del sistema.
     * Retorna 204 No Content (sin cuerpo) como buena práctica REST.
     */
    public function destroy($id)
    {
        Solicitud::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
