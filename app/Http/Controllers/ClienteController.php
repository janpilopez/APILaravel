<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{

    public function index()
    {
        return Cliente::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombres' => 'required',
            'apellidos' => 'required'
        ]);
        $cliente = new Cliente();
        $cliente->nombres = $request->nombres;
        $cliente->apellidos = $request->input('apellidos');
        $cliente->save();
        return $cliente;
    }

    public function show(Cliente $cliente)
    {
        //en este caso ya esta buscando el cliente por id, y con la clase Cliente lo busca y por tanto ya tenemos el cliente
        //la forma anterior era $cliente = Cliente::find($id);
        return Cliente::find($cliente);
    }

    public function update(Request $request, Cliente $cliente)
    {
        //el cliente ya esta siendo buscado con cliente y es encontrado por el modelo Cliente
        $request->validate([
            'nombres' => 'required',
            'apellidos' => 'required'
        ]);
        $cliente->nombres = $request->nombres;
        $cliente->apellidos = $request->apellidos;
        $cliente->save();
        return $cliente;
    }

    // public function destroy(Cliente $cliente)
    public function destroy($id)
    {
        $cliente = Cliente::find($id);
        if (\is_null($cliente)) {
            return response()->json(['mensaje' => 'Cliente no encontrado'], 404);
        }
        $cliente->delete();
        // return response()->json(['mensaje' => 'Cliente eliminado']);
        return response()->noContent(); //estado 204, se proceso correctamente pero no devuelve nada.
    }
}
