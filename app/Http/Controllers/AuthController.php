<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Log masuk guna username (nama penuh huruf besar) + kata laluan.
     */
    public function login(Request $request)
    {
        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');

        if (! $username || ! $password) {
            return back()->with('error', t('Sila isi username dan kata laluan.', 'Please fill in your username and password.'));
        }

        // Had cubaan: 5 kali gagal seminit bagi setiap username + IP
        $throttleKey = 'login|'.strtoupper($username).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->with('error', sprintf(
                t('Terlalu banyak cubaan log masuk. Cuba lagi dalam %d saat.', 'Too many login attempts. Try again in %d seconds.'),
                RateLimiter::availableIn($throttleKey)
            ));
        }

        $user = User::where('username', strtoupper($username))->first();

        if (! $user || ! password_verify($password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->with('error', t('Username atau kata laluan tidak sah.', 'Invalid username or password.'));
        }
        RateLimiter::clear($throttleKey);

        if ($user->status !== 'active') {
            return back()->with('error', t('Akaun anda tidak aktif. Sila hubungi pentadbir.', 'Your account is inactive. Please contact the administrator.'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        DB::table('users')->where('id', $user->id)->update(['last_login' => DB::raw('NOW()')]);

        return redirect(dashboard_route_for($user->role));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Tetapan semula sendiri (username + emel) telah dibuang kerana sesiapa boleh mengambil alih akaun.
     * Halaman ini hanya memaklumkan pengguna supaya menghubungi admin.
     */
    public function showReset()
    {
        return view('auth.reset_password');
    }
}
