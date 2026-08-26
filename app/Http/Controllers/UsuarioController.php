<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function($q) use ($b) {
                $q->where('name', 'LIKE', "%$b%")
                  ->orWhere('username', 'LIKE', "%$b%")
                  ->orWhere('email', 'LIKE', "%$b%");
            });
        }
        $usuarios = $query->orderBy('name')->paginate(15);
        $roles = Role::where('activo', true)->get();
        return view('usuarios.index', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'telefono' => 'nullable|string|max:30',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['activo'] = true;
        User::create($data);
        return back()->with('success', 'Usuario creado correctamente');
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,' . $usuario->id,
            'email' => 'required|email|max:255|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'required|exists:roles,id',
            'telefono' => 'nullable|string|max:30',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['activo'] = $request->boolean('activo', true);
        $usuario->update($data);
        return back()->with('success', 'Usuario actualizado');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puede eliminar su propia cuenta');
        }
        $usuario->update(['activo' => false]);
        return back()->with('success', 'Usuario desactivado');
    }

    public function roles()
    {
        $roles = Role::withCount('users')->orderBy('nombre')->get();
        return view('usuarios.roles', compact('roles'));
    }

    public function storeRol(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:80|unique:roles,nombre',
            'descripcion' => 'nullable|string|max:255',
            'permisos' => 'nullable|array',
        ]);
        $data['activo'] = true;
        Role::create($data);
        return back()->with('success', 'Rol creado correctamente');
    }

    public function updateRol(Request $request, Role $rol)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:80|unique:roles,nombre,' . $rol->id,
            'descripcion' => 'nullable|string|max:255',
            'permisos' => 'nullable|array',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        $rol->update($data);
        return back()->with('success', 'Rol actualizado');
    }
}
