<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('rol')->orderBy('name')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'rol'       => 'required|in:admin,ventas,produccion',
            'modulos'   => 'nullable|array',
            'modulos.*' => 'in:' . implode(',', array_keys(User::MODULOS)),
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['modulos']  = $this->modulosDesde($request, $data['rol']);

        User::create($data);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($usuario->id)],
            'password'  => 'nullable|string|min:8|confirmed',
            'rol'       => 'required|in:admin,ventas,produccion',
            'modulos'   => 'nullable|array',
            'modulos.*' => 'in:' . implode(',', array_keys(User::MODULOS)),
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // El Administrador principal siempre tiene acceso total: no se le tocan módulos.
        if (! $usuario->esSuper()) {
            $data['modulos'] = $this->modulosDesde($request, $data['rol']);
        } else {
            unset($data['modulos']);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado.');
    }

    /**
     * Cierra todas las sesiones activas de un usuario (por si quedó trabado en
     * otra PC). Solo el Administrador principal llega acá (middleware modulo.access).
     */
    public function cerrarSesiones(User $usuario)
    {
        // Invalida la sesión activa: su session_id deja de coincidir → se cierra
        // en el próximo request de esa PC.
        $usuario->update(['session_id' => 'FORCED_' . Str::random(24)]);

        return back()->with('success', 'Se cerró la sesión de ' . $usuario->name . ' (se le pedirá ingresar de nuevo).');
    }

    /** Módulos elegidos en el form (o los default del rol si no vino la sección). */
    private function modulosDesde(Request $request, string $rol): array
    {
        if (! $request->boolean('modulos_marcado')) {
            return User::modulosPorRol($rol);
        }

        return array_values(array_intersect(
            (array) $request->input('modulos', []),
            array_keys(User::MODULOS)
        ));
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No podés eliminar tu propia cuenta.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado.');
    }
}
