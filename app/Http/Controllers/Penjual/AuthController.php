<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
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
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

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

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau kata sandi salah.']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Koma desimal (-8,2192) diterima sebagai titik; email disamakan ke huruf kecil.
        $request->merge(array_map(
            fn ($v) => is_string($v) ? str_replace(',', '.', trim($v)) : $v,
            $request->only(['lat', 'lng'])
        ));
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'regex:/[A-Za-z]/', 'regex:/\d/'],
            'business_name' => ['required', 'string', 'max:160'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => [Rule::in(array_keys(Product::CATEGORIES))],
            'whatsapp' => ['required', 'max:20', 'regex:/^\d[\d\s-]{7,}$/'],
            'business_email' => ['nullable', 'email', 'max:160'],
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
            'password.max' => 'Kata sandi maksimal 72 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.regex' => 'Kata sandi harus kombinasi huruf dan angka.',
            'business_name.required' => 'Nama usaha wajib diisi.',
            'categories.required' => 'Pilih minimal satu kategori.',
            'categories.min' => 'Pilih minimal satu kategori.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.regex' => 'Nomor WhatsApp tidak valid.',
            'whatsapp.max' => 'Nomor WhatsApp tidak valid.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'lat.required' => 'Tentukan titik koordinat lapak di peta.',
            'lng.required' => 'Tentukan titik koordinat lapak di peta.',
            'lat.numeric' => 'Latitude tidak valid.',
            'lng.numeric' => 'Longitude tidak valid.',
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        $categories = array_values(array_unique($data['categories']));

        $tenant = DB::transaction(function () use ($data, $categories) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'penjual',
                'email_verified_at' => now(),
            ]);

            $tenant = Tenant::create([
                'user_id' => $user->id,
                'code' => 'TMP'.Str::upper(Str::random(20)), // sementara, diganti dari ID di bawah
                'name' => $data['business_name'],
                'slug' => $this->uniqueSlug($data['business_name']),
                'owner_name' => $data['name'],
                'whatsapp' => $this->normalizeWhatsapp($data['whatsapp']),
                'email' => ($data['business_email'] ?? null) ?: $data['email'],
                'categories' => $categories,
                'main_category' => Product::CATEGORIES[$categories[0]] ?? null,
                'kecamatan' => $data['kecamatan'] ?? '',
                'desa' => $data['desa'] ?? null,
                'address' => $data['address'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            $code = 'TKT-'.now()->format('Y').'-'.str_pad((string) $tenant->id, 4, '0', STR_PAD_LEFT);
            if (Tenant::where('code', $code)->where('id', '!=', $tenant->id)->exists()) {
                $code .= '-'.Str::upper(Str::random(3));
            }
            $tenant->update(['code' => $code]);

            return $tenant;
        });

        Auth::login($tenant->user);
        $request->session()->regenerate();

        return redirect()->route('penjual.dashboard')
            ->with('ok', 'Pendaftaran berhasil. Selamat datang di Bolo Tani, lapak Anda sudah aktif!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'lapak';
        $slug = $base;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }

    /** Hasil selalu berbentuk 08xxxxxxxx, juga bila pengguna mengetik 62 atau 0 di depan. */
    private function normalizeWhatsapp(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);
        if (str_starts_with($digits, '62')) {
            $digits = substr($digits, 2);
        }

        return '0'.ltrim($digits, '0');
    }
}