<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users', 'permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.form', [
            'role' => new Role,
            'permissions' => Permission::orderBy('group')->get()->groupBy('group'),
            'assigned' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:80',
            'description'   => 'nullable|string|max:200',
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $role = Role::create(['name' => $data['name'], 'description' => $data['description'] ?? null]);
        $role->permissions()->sync($data['permissions'] ?? []);
        return redirect()->route('admin.roles.index')->with('toast', '✓ Role ditambahkan');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.form', [
            'role' => $role,
            'permissions' => Permission::orderBy('group')->get()->groupBy('group'),
            'assigned' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:80',
            'description'   => 'nullable|string|max:200',
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        $role->update(['name' => $data['name'], 'description' => $data['description'] ?? null]);
        // role terkunci (super admin) tetap full akses; tetap simpan pilihan
        $role->permissions()->sync($data['permissions'] ?? []);
        return redirect()->route('admin.roles.index')->with('toast', '✓ Role diperbarui');
    }

    public function destroy(Role $role)
    {
        if ($role->is_locked) {
            return back()->with('error', 'Role inti tidak bisa dihapus.');
        }
        if ($role->users()->exists()) {
            return back()->with('error', 'Role masih dipakai pengguna.');
        }
        $role->delete();
        return back()->with('toast', 'Role dihapus');
    }
}
