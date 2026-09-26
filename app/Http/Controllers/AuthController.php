<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

        $user = User::where('username', strtoupper($username))->first();

        if (! $user || ! password_verify($password, $user->password)) {
            return back()->with('error', t('Username atau kata laluan tidak sah.', 'Invalid username or password.'));
        }

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

    public function showReset()
    {
        return view('auth.reset_password');
    }

    /**
     * Tetapkan semula kata laluan dengan sahkan username + emel berdaftar.
     */
    public function reset(Request $request)
    {
        $username = trim((string) $request->input('username', ''));
        $email = trim((string) $request->input('email', ''));
        $newPassword = (string) $request->input('new_password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        $error = null;
        if (! $username || ! $email || ! $newPassword || ! $confirmPassword) {
            $error = t('Sila isi semua ruangan.', 'Please fill in all fields.');
        } elseif (strlen($newPassword) < 6) {
            $error = t('Kata laluan baharu mesti sekurang-kurangnya 6 aksara.', 'New password must be at least 6 characters.');
        } elseif ($newPassword !== $confirmPassword) {
            $error = t('Pengesahan kata laluan tidak sepadan.', 'Password confirmation does not match.');
        } else {
            $user = DB::table('users')->select('id', 'username')->whereRaw('LOWER(email) = LOWER(?)', [$email])->first();

            if (! $user || strcasecmp(trim($user->username), $username) !== 0) {
                // Mesej generik - tidak dedahkan sama ada emel atau username yang tidak sepadan
                $error = t('Username dan emel tidak sepadan dengan mana-mana akaun.', 'Username and email do not match any account.');
            } else {
                DB::table('users')->where('id', $user->id)->update(['password' => Hash::make($newPassword)]);

                return back()->with('success', t('Kata laluan anda telah ditetapkan semula. Sila log masuk dengan kata laluan baharu.', 'Your password has been reset. Please log in with your new password.'));
            }
        }

        return back()->withInput($request->only('username', 'email'))->with('error', $error);
    }
}
