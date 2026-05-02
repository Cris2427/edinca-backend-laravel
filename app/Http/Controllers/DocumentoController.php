<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Mail\DocumentoSubido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * DocumentoController
 *
 * Gestiona la subida, listado y eliminación de documentos PDF
 * asociados a proyectos.
 *
 * Flujo de subida:
 * 1. Admin selecciona un proyecto y sube un PDF desde el panel
 * 2. Se valida que sea realmente un PDF (extensión + magic bytes)
 * 3. Se guarda con nombre UUID para evitar colisiones
 * 4. Se envía automáticamente al correo del cliente como adjunto
 */
class DocumentoController extends Controller
{
    /**
     * GET /api/documentos
     *
     * Lista todos los documentos con proyecto y cliente asociados.
     */
    public function index()
    {
        return response()->json(
            Documento::with('proyecto.cliente')
                     ->orderBy('created_at', 'desc')
                     ->get()
        );
    }

    /**
     * GET /api/documentos/proyecto/{proyectoId}
     *
     * Lista los documentos de un proyecto específico.
     */
    public function porProyecto($proyectoId)
    {
        return response()->json(
            Documento::with('proyecto.cliente')
                     ->where('proyecto_id', $proyectoId)
                     ->orderBy('created_at', 'desc')
                     ->get()
        );
    }

    /**
     * POST /api/documentos/upload
     *
     * Sube un archivo PDF al servidor y lo asocia a un proyecto.
     *
     * Validaciones de seguridad:
     * - Solo acepta archivos con extensión .pdf (mime: application/pdf)
     * - Máximo 10MB
     * - Valida magic bytes (%PDF-) para prevenir que se cambie la extensión
     *   a un archivo malicioso
     *
     * El archivo se guarda en storage/app/public/documentos/ con nombre UUID.
     * Se puede acceder via /storage/documentos/{uuid}.pdf
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file'        => 'required|file|mimes:pdf|max:10240',
            'proyecto_id' => 'required|exists:proyectos,id',
        ]);

        $archivo = $request->file('file');

        // Validación extra: leer los primeros 5 bytes del archivo
        // Un PDF válido siempre empieza con "%PDF-" (hex: 25 50 44 46 2D)
        $handle = fopen($archivo->getPathname(), 'rb');
        $header = fread($handle, 5);
        fclose($handle);

        if ($header !== '%PDF-') {
            return response()->json(
                ['message' => 'El archivo no es un PDF válido'],
                422
            );
        }

        // Nombre único basado en UUID para evitar colisiones y sobreescrituras
        $nombreGuardado = Str::uuid() . '.pdf';

        // Guarda en storage/app/public/documentos/ (accesible via symlink)
        $ruta = $archivo->storeAs('documentos', $nombreGuardado, 'public');

        $documento = Documento::create([
            'nombre_original' => $archivo->getClientOriginalName(),
            'nombre_guardado' => $nombreGuardado,
            'ruta'            => $ruta,
            'tamano'          => $archivo->getSize(),
            'proyecto_id'     => $request->proyecto_id,
        ]);

        // Carga las relaciones para la respuesta y para el correo
        $documento->load('proyecto.cliente');

        // Envía el PDF por correo al cliente como adjunto
        $cliente = $documento->proyecto?->cliente;
        if ($cliente && $cliente->email) {
            try {
                Mail::to($cliente->email)->send(
                    new DocumentoSubido($documento, $archivo->getPathname())
                );
            } catch (\Exception $e) {
                Log::error('Error enviando correo documento ID ' . $documento->id . ': ' . $e->getMessage());
            }
        }

        return response()->json($documento, 201);
    }

    /**
     * DELETE /api/documentos/{id}
     *
     * Elimina el registro de BD y el archivo físico del servidor.
     * Usa Storage::disk('public') para apuntar al disco correcto.
     */
    public function destroy($id)
    {
        $documento = Documento::findOrFail($id);

        // Elimina el archivo físico antes de borrar el registro
        Storage::disk('public')->delete('documentos/' . $documento->nombre_guardado);

        $documento->delete();
        return response()->json(null, 204);
    }
}
