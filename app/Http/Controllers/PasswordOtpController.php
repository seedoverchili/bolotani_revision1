<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Lupa kata sandi penjual: kode OTP via email, lalu langsung masuk. */
class PasswordOtpController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function request()
    {
        return view('auth.forgot');
    }

    public function send(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);
        $email = strtolower($data['email']);

        // Respons sama untuk email terdaftar maupun tidak, agar akun tidak bisa ditebak.
        if (User::where('email', $email)->where('role', 'penjual')->exists()) {
            $this->otp->issue($email, OtpMail::LOGIN);
        }

        $request->session()->put('otp_email', $email);

        return redirect()->route('password.otp')->with('status', 'Jika email terdaftar, kode verifikasi telah dikirim.');
    }

    public function form(Request $request)
    {
        $email = $request->session()->get('otp_email');
        abort_unless($email, 404);

        return view('auth.otp', [
            'email' => $email,
            'seconds' => $this->otp->secondsUntilResend($email, OtpMail::LOGIN),
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

        if ($error = $this->otp->verify($email, OtpMail::LOGIN, $request->input('code'))) {
            return back()->withErrors(['code' => $error]);
        }

        $user = User::where('email', $email)->where('role', 'penjual')->first();
        abort_unless($user, 404);

        // Menguasai kode di email = membuktikan kepemilikan email.
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $request->session()->forget('otp_email');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('penjual.dashboard');
    }
}
