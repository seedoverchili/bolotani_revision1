<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminPasswordChangedMail;
use App\Mail\AdminResetLinkMail;
use App\Models\User;
use App\Services\Mailer;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
// 2.5 — Bagian 2 · Masuk admin — Siapkan layanan autentikasi. Fungsi: Memuat layanan sesi, broker token, aturan sandi, serta kedua email khusus administrator.

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }
    // 2.6 — Bagian 2 · Masuk admin — Buka formulir masuk. Fungsi: Menampilkan formulir masuk khusus admin.

    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']], [
            'email.required' => 'Email administrator wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);
        // 2.11 — Bagian 2 · Masuk admin — Validasi isian login. Fungsi: Memastikan email dan sandi berbentuk data yang dapat diperiksa oleh autentikasi Laravel.

        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password'], 'role' => 'admin'])) {
            $request->session()->regenerate();

            return to_route('admin.dashboard');
        }

        return back()->withInput($request->only('email'))->withErrors(['email' => 'Email atau kata sandi salah.']);
    }
    // 2.12 — Bagian 2 · Masuk admin — Periksa kredensial dan buat sesi. Fungsi: Memeriksa email, sandi, dan peran admin sekaligus. Sesi baru dibuat setelah berhasil masuk.

    public function forgot()
    {
        return view('admin.forgot');
    }
    // 7.2 — Bagian 7 · Pemulihan sandi — Buka formulir lupa sandi. Fungsi: Membuka formulir untuk meminta tautan pemulihan melalui email.

    public function sendLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'Email administrator wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        Password::sendResetLink(['email' => $request->input('email'), 'role' => 'admin'], function (User $user, string $token) {
            $url = route('admin.password.reset', ['token' => $token, 'email' => $user->email]);
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
            Mailer::send($user->email, new AdminResetLinkMail($url, $minutes));
        });
        // 7.5 — Bagian 7 · Pemulihan sandi — Buat token dan kirim tautan. Fungsi: Broker membuat token sementara untuk akun admin. Tautan email mengarah langsung ke formulir admin.

        return back()->with('status', 'Jika email terdaftar sebagai administrator, tautan pemulihan telah dikirim.');
    }
    // 7.12 — Bagian 7 · Pemulihan sandi — Kembalikan pesan permintaan. Fungsi: Mengembalikan pesan yang sama untuk semua alamat sehingga status akun tetap privat.

    public function resetForm(Request $request, string $token)
    {
        return view('admin.reset', ['token' => $token, 'email' => $request->string('email')->toString()]);
    }
    // 7.16 — Bagian 7 · Pemulihan sandi — Buka formulir dari tautan email. Fungsi: Membawa token dari URL dan email penerima ke formulir penggantian sandi.

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.mixed' => 'Kata sandi harus mengandung huruf besar dan huruf kecil.',
            'password.numbers' => 'Kata sandi harus mengandung angka.',
            'password.symbols' => 'Kata sandi harus mengandung simbol khusus.',
        ]);
        // 7.20 — Bagian 7 · Pemulihan sandi — Validasi sandi baru. Fungsi: Memeriksa kesamaan kedua isian serta syarat minimal delapan karakter, huruf besar dan kecil, angka, dan simbol.

        $status = Password::reset(
            $data + ['role' => 'admin'],
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
                Mailer::send($user->email, new AdminPasswordChangedMail($user->name, now()));
            }
        );
        // 7.21 — Bagian 7 · Pemulihan sandi — Periksa token dan simpan sandi. Fungsi: Broker memeriksa token dan peran admin. Model menyimpan hash sandi baru, mengganti remember_token, lalu mengirim pemberitahuan.

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['password' => 'Tautan tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.']);
        }

        return redirect()->route('admin.password.done');
    }
    // 7.28 — Bagian 7 · Pemulihan sandi — Tentukan halaman hasil reset. Fungsi: Menampilkan kesalahan untuk token tidak valid atau halaman berhasil jika perubahan tersimpan.
}
