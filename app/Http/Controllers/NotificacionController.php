<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    /** GET /api/notificaciones */
    public function index()
    {
        return response()->json(
            Notificacion::orderBy('created_at', 'desc')->get()
        );
    }

    /** GET /api/notificaciones/no-leidas */
    public function noLeidas()
    {
        return response()->json(
            Notificacion::where('leida', false)->orderBy('created_at', 'desc')->get()
        );
    }

    /** GET /api/notificaciones/conteo */
    public function conteo()
    {
        return response()->json([
            'noLeidas' => Notificacion::where('leida', false)->count(),
        ]);
    }

    /** PATCH /api/notificaciones/{id}/leer */
    public function marcarLeida($id)
    {
        Notificacion::findOrFail($id)->update(['leida' => true]);
        return response()->json(null, 200);
    }

    /** PATCH /api/notificaciones/leer-todas */
    public function marcarTodasLeidas()
    {
        Notificacion::where('leida', false)->update(['leida' => true]);
        return response()->json(null, 200);
    }
}
