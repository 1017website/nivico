<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:60',
            'last_name'  => 'nullable|string|max:60',
            'email'      => 'required|email|max:160|unique:users,email',
            'phone'      => 'nullable|string|max:30',
            'password'   => ['required', 'confirmed', Password::min(8)],
            'agree'      => 'accepted',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'] ?? null,
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'password'   => $data['password'],
            'role'       => 'customer',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('toast', '✓ Akun berhasil dibuat. Selamat datang!');
    }
}
