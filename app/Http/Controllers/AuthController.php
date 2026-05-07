<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * AuthController
 *
 * Maneja la autenticación de administradores usando Laravel Sanctum.
 * Sanctum genera tokens de API que el frontend incluye en cada petición
 * protegida en el header: Authorization: Bearer {token}
 */
class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     *
     * Valida credenciales y retorna un token de acceso.
     * El token se guarda en localStorage del frontend y se envía
     * en cada petición a rutas protegidas.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Busca el usuario por email y que esté activo
        $usuario = Usuario::where('email', $request->email)
                          ->where('activo', true)
                          ->first();

        // Verifica que exista y que la contraseña coincida (bcrypt)
        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        // Elimina tokens anteriores (una sola sesión activa por usuario)
        $usuario->tokens()->delete();

        // Crea un nuevo token Sanctum
        $token = $usuario->createToken('edinca-token')->plainTextToken;

        // Construye la URL pública de la foto si existe
        $fotoUrl = $usuario->foto
            ? rtrim(env('APP_URL'), '/') . '/storage/' . $usuario->foto
            : null;

        return response()->json([
            'token'    => $token,
            'nombre'   => $usuario->nombre,
            'email'    => $usuario->email,
            'rol'      => $usuario->rol,
            'foto_url' => $fotoUrl,
        ]);
    }

    /**
     * POST /api/auth/logout
     *
     * Revoca el token actual del usuario autenticado.
     * El frontend debe eliminar el token de localStorage después.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    /**
     * POST /api/auth/cambiar-password
     *
     * Permite al admin cambiar su contraseña desde el panel.
     * Requiere la contraseña actual para verificar identidad.
     */
    public function cambiarPassword(Request $request)
    {
        // El frontend envía passwordNueva (acepta ambas variantes por compatibilidad)
        $request->validate([
            'passwordActual' => 'required|string',
            'passwordNueva'  => 'required|string|min:6',
        ]);

        $usuario = $request->user();

        // Verifica que la contraseña actual sea correcta antes de cambiar
        if (!Hash::check($request->passwordActual, $usuario->password)) {
            return response()->json(['message' => 'Contraseña actual incorrecta'], 400);
        }

        // Guarda la nueva contraseña hasheada con bcrypt
        $usuario->update(['password' => Hash::make($request->passwordNueva)]);

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }

    /**
     * POST /api/auth/foto
     *
     * Sube o reemplaza la foto de perfil del admin autenticado.
     * Guarda el archivo en storage/app/public/fotos/ y devuelve la URL pública.
     */
    public function subirFoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|max:2048', // máx 2 MB
        ]);

        $usuario = $request->user();

        // Elimina la foto anterior si existe
        if ($usuario->foto) {
            Storage::disk('public')->delete($usuario->foto);
        }

        // Guarda la nueva foto con nombre UUID
        $archivo  = $request->file('foto');
        $nombre   = 'fotos/' . Str::uuid() . '.' . $archivo->getClientOriginalExtension();
        Storage::disk('public')->put($nombre, file_get_contents($archivo));

        // Guarda la ruta en la BD
        $usuario->update(['foto' => $nombre]);

        $fotoUrl = rtrim(env('APP_URL'), '/') . '/storage/' . $nombre;

        return response()->json(['foto_url' => $fotoUrl]);
    }

    /**
     * DELETE /api/auth/foto
     *
     * Elimina la foto de perfil del admin (vuelve al avatar por defecto).
     */
    public function eliminarFoto(Request $request)
    {
        $usuario = $request->user();

        if ($usuario->foto) {
            Storage::disk('public')->delete($usuario->foto);
            $usuario->update(['foto' => null]);
        }

        return response()->json(['foto_url' => null]);
    }
}
