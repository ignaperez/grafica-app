<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\ListaPrecio;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::with('listaPrecio')->orderBy('nombre')->get();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $listas = ListaPrecio::orderBy('nombre')->get();
        return view('clientes.create', compact('listas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'telefono'        => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'direccion'       => 'nullable|string',
            'lista_precio_id' => 'required|exists:lista_precios,id',
        ]);

        Cliente::create($request->only(['nombre', 'telefono', 'email', 'direccion', 'lista_precio_id']));

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load('listaPrecio', 'ordenesTrabajo');
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        $listas = ListaPrecio::orderBy('nombre')->get();
        return view('clientes.edit', compact('cliente', 'listas'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'telefono'        => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'direccion'       => 'nullable|string',
            'lista_precio_id' => 'required|exists:lista_precios,id',
        ]);

        $cliente->update($request->only(['nombre', 'telefono', 'email', 'direccion', 'lista_precio_id']));

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }

    /**
     * Búsqueda AJAX para Select2.
     * GET /clientes/search?q=term
     */
    public function search(Request $request)
    {
        $term = $request->get('q');

        $clientes = Cliente::where('nombre', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get();

        return response()->json(
            $clientes->map(fn ($c) => [
                'id'   => $c->id,
                'text' => $c->nombre,
            ])
        );
    }
}
