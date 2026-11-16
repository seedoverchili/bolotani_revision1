<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Penjual\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/** Lupa kata sandi pedagang: kode OTP via email, lalu langsung masuk. */
class PasswordOtpController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function request()
    {
        return view('auth.forgot');
    }

    public function send(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate(['email' => ['required', 'email', 'max:160']], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email terlalu panjang.',
        ]);
        $email = $data['email'];

        // Hanya akun pedagang yang dikirimi kode, tetapi respons dan tampilan selalu sama
        // supaya orang luar tidak bisa menebak email mana yang terdaftar.
        if (User::where('email', $email)->where('role', 'penjual')->exists()) {
            $this->otp->issue($email);
        }

        // Hitung mundur diambil dari session (bukan dari database) dengan alasan yang sama.
        $session = $request->session();
        $sentAt = (int) $session->get('otp_sent_at', 0);
        $stillCooling = $session->get('otp_email') === $email
            && $sentAt > 0
            && now()->timestamp - $sentAt < OtpService::RESEND_SECONDS;

        if (! $stillCooling) {
            $session->put('otp_sent_at', now()->timestamp);
        }
        $session->put('otp_email', $email);

        return redirect()->route('password.otp')
            ->with('status', 'Jika email terdaftar, kode verifikasi telah dikirim.');
    }

    public function form(Request $request)
    {
        $email = $request->session()->get('otp_email');
        abort_unless($email, 404);

        $sentAt = (int) $request->session()->get('otp_sent_at', 0);
        $seconds = max(0, OtpService::RESEND_SECONDS - (now()->timestamp - $sentAt));

        return view('auth.otp', [
            'email' => $email,
            'seconds' => $seconds,
            'action' => route('password.otp.verify'),
            'resend' => route('password.email'),
            'backUrl' => route('password.request'),
            'backLabel' => 'Ganti Email',
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']], [
            'code.required' => 'Masukkan 6 digit kode verifikasi.',
            'code.digits' => 'Kode verifikasi harus 6 digit.',
        ]);

        $email = $request->session()->get('otp_email');
        abort_unless($email, 404);

        if ($error = $this->otp->verify($email, (string) $request->input('code'))) {
            return back()->withErrors(['code' => $error]);
        }

        $user = User::where('email', $email)->where('role', 'penjual')->first();
        abort_unless($user, 404);

        // Menguasai kode di email = membuktikan kepemilikan email.
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $request->session()->forget(['otp_email', 'otp_sent_at']);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('penjual.dashboard');
    }
}
