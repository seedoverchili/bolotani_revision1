<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials + ['role' => 'penjual'], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('penjual.dashboard'));
        }

        return back()->withInput($request->only('email'))->withErrors(['email' => 'Email atau kata sandi salah.']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Koma desimal (-8,2192) diterima sebagai titik.
        $request->merge(array_map(fn ($v) => is_string($v) ? str_replace(',', '.', trim($v)) : $v, $request->only(['lat', 'lng'])));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Za-z]/', 'regex:/\d/'],
            'business_name' => ['required', 'string', 'max:160'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => [Rule::in(array_keys(Product::CATEGORIES))],
            'whatsapp' => ['required', 'regex:/^\d[\d\s-]{7,}$/'],
            'business_email' => ['nullable', 'email'],
            'kecamatan' => ['nullable', 'string', 'max:80'],
            'desa' => ['nullable', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:500'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'agree' => ['accepted'],
        ], [
            'name.required' => 'Nama penanggung jawab wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.regex' => 'Kata sandi harus kombinasi huruf dan angka.',
            'business_name.required' => 'Nama usaha wajib diisi.',
            'categories.required' => 'Pilih minimal satu kategori.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid.',
            'lat.required' => 'Tentukan titik koordinat lapak di peta.',
            'lng.required' => 'Tentukan titik koordinat lapak di peta.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        $wa = '0'.ltrim(preg_replace('/\D+/', '', $data['whatsapp']), '0');

        $tenant = DB::transaction(function () use ($data, $wa) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'penjual',
                'email_verified_at' => now(),
            ]);

            $slug = Str::slug($data['business_name']);
            if (Tenant::where('slug', $slug)->exists()) {
                $slug .= '-'.Str::lower(Str::random(4));
            }

            return Tenant::create([
                'user_id' => $user->id,
                'code' => 'TKT-'.now()->format('Y').'-'.str_pad((string) (Tenant::max('id') + 1), 4, '0', STR_PAD_LEFT),
                'name' => $data['business_name'],
                'slug' => $slug,
                'owner_name' => $data['name'],
                'whatsapp' => $wa,
                'email' => ($data['business_email'] ?? null) ?: $data['email'],
                'categories' => $data['categories'],
                'main_category' => Product::CATEGORIES[$data['categories'][0]] ?? null,
                'kecamatan' => $data['kecamatan'] ?? '',
                'desa' => $data['desa'] ?? null,
                'address' => $data['address'],
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        });

        Auth::login($tenant->user);
        $request->session()->regenerate();

        return redirect()->route('penjual.dashboard')
            ->with('ok', 'Pendaftaran berhasil. Selamat datang di Bolo Tani, lapak Anda sudah aktif!');
    }

    public function logout(Request $request)
    {
        $admin = $request->user()?->isAdmin();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($admin ? 'admin.login' : 'login');
    }
}
