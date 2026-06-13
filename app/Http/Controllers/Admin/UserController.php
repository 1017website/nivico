<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role')->latest();
        if ($request->filled('q')) {
            $query->where(function ($w) use ($request) {
                $w->where('first_name', 'like', '%'.$request->q.'%')
                  ->orWhere('last_name', 'like', '%'.$request->q.'%')
                  ->orWhere('email', 'like', '%'.$request->q.'%');
            });
        }
        $users = $query->paginate(15)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User, 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name'  => 'nullable|string|max:60',
            'email'      => 'required|email|max:160|unique:users,email',
            'phone'      => 'nullable|string|max:30',
            'role_id'    => 'required|exists:roles,id',
            'password'   => ['required', 'confirmed', Password::min(8)],
            'is_active'  => 'nullable|boolean',
        ]);
        $data['role'] = 'admin'; // tandai sebagai staf admin
        $data['is_active'] = $request->boolean('is_active', true);
        User::create($data);
        return redirect()->route('admin.users.index')->with('toast', '✓ Pengguna ditambahkan');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', ['user' => $user, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name'  => 'nullable|string|max:60',
            'email'      => 'required|email|max:160|unique:users,email,'.$user->id,
            'phone'      => 'nullable|string|max:30',
            'role_id'    => 'required|exists:roles,id',
            'password'   => ['nullable', 'confirmed', Password::min(8)],
            'is_active'  => 'nullable|boolean',
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $data['is_active'] = $request->boolean('is_active', true);
        $user->update($data);
        return redirect()->route('admin.users.index')->with('toast', '✓ Pengguna diperbarui');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        return back()->with('toast', 'Pengguna dihapus');
    }
}
