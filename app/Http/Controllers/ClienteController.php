<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /** GET /api/clientes */
    public function index()
    {
        return response()->json(
            Cliente::orderBy('created_at', 'desc')->get()
        );
    }

    /** GET /api/clientes/{id} */
    public function show($id)
    {
        return response()->json(Cliente::findOrFail($id));
    }

    /** POST /api/clientes */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:255',
            'email'     => 'nullable|email|unique:clientes,email',
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'rut'       => 'nullable|string|max:20',
        ]);

        $cliente = Cliente::create($data);
        return response()->json($cliente, 201);
    }

    /** PUT /api/clientes/{id} */
    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $data = $request->validate([
            'nombre'    => 'sometimes|required|string|max:255',
            'email'     => 'nullable|email|unique:clientes,email,' . $id,
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'rut'       => 'nullable|string|max:20',
        ]);

        $cliente->update($data);
        return response()->json($cliente);
    }

    /** DELETE /api/clientes/{id} */
    public function destroy($id)
    {
        Cliente::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
