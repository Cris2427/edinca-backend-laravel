<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        return response()->json([
            'token'  => $token,
            'nombre' => $usuario->nombre,
            'email'  => $usuario->email,
            'rol'    => $usuario->rol,
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
}
