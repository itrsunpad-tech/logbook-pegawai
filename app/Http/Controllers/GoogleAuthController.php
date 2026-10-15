<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user ke halaman login Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->with([
                'prompt' => 'select_account',
            ])
            ->redirect();
    }

        /**
     * Menangani callback dari Google.
     * Hanya email yang sudah didaftarkan HC yang boleh masuk.
     */
    public function callback()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();

        $email = Str::lower(trim((string) $googleUser->getEmail()));

        // PENTING: jangan lanjut kalau email kosong. Di Laravel,
        // where('email', null) berubah menjadi "email IS NULL" dan akan cocok
        // dengan pegawai hasil impor yang belum punya email.
        $user = $email !== ''
            ? User::where('email', $email)->first()
            : null;

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('error', "Email {$email} belum terdaftar di sistem logbook. Hubungi HC RS UNPAD untuk mendaftarkan emailmu.");
        }

        // Hanya perbarui data dari Google. 'name' sengaja TIDAK ditimpa lagi,
        // supaya nama lengkap yang sudah diedit HC tidak berubah setiap login.
        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar'    => $googleUser->getAvatar(),
        ]);

        Auth::login($user);

        request()->session()->regenerate();

        return redirect()->route(
            $user->isAdmin() ? 'admin.dashboard' : 'logbook.index'
        );
    }
}