<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    /** GET /api/proyectos */
    public function index()
    {
        return response()->json(
            Proyecto::with('cliente')->orderBy('created_at', 'desc')->get()
        );
    }

    /** GET /api/proyectos/{id} */
    public function show($id)
    {
        return response()->json(
            Proyecto::with('cliente')->findOrFail($id)
        );
    }

    /** POST /api/proyectos */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'              => 'required|string|max:255',
            'tipo'                => 'required|in:CONSTRUCCION_NUEVA,AMPLIACION,REGULARIZACION,REMODELACION',
            'descripcion'         => 'nullable|string',
            'metros_cuadrados'    => 'nullable|numeric|min:0',
            'numero_trabajadores' => 'nullable|integer|min:0',
            'fecha_inicio'        => 'nullable|date',
            'fecha_fin_estimada'  => 'nullable|date',
            'cliente_id'          => 'required|exists:clientes,id',
        ]);

        $proyecto = Proyecto::create($data);

        return response()->json(
            Proyecto::with('cliente')->find($proyecto->id), 201
        );
    }

    /** PATCH /api/proyectos/{id}/estado */
    public function actualizarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:PENDIENTE,EN_PROCESO,EN_EJECUCION,FINALIZADO,CANCELADO',
        ]);

        $proyecto = Proyecto::findOrFail($id);
        $proyecto->update(['estado' => $request->estado]);

        return response()->json(Proyecto::with('cliente')->find($id));
    }

    /** DELETE /api/proyectos/{id} */
    public function destroy($id)
    {
        Proyecto::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
