<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($cred, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda dinonaktifkan.'])->onlyInput('email');
            }

            // catat login untuk staf admin
            if ($user->isAdmin()) {
                $this->log('login', 'Masuk ke panel admin', $user);
            }

            $to = $user->isAdmin() ? route('admin.dashboard') : route('home');
            return redirect()->intended($to)->with('toast', '✓ Login berhasil! Selamat datang kembali.');
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->isAdmin()) {
            $this->log('logout', 'Keluar dari panel admin', $user);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    protected function log(string $action, string $desc, $user): void
    {
        ActivityLog::create([
            'user_id'    => $user->id,
            'user_name'  => $user->name,
            'action'     => $action,
            'description'=> $desc,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
