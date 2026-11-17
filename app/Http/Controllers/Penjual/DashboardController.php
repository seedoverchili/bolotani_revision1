<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $products = $tenant->products()->with('images')->orderBy('sku')->get();

        return view('penjual.dashboard', [
            'tenant' => $tenant,
            'products' => $products,
            'ready' => $products->where('stock', '>', 0)->count(),
            'out' => $products->where('stock', '<=', 0)->count(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        // Koma desimal (-8,2192) diterima sebagai titik.
        $request->merge(array_map(fn ($v) => is_string($v) ? str_replace(',', '.', trim($v)) : $v, $request->only(['lat', 'lng'])));

        $tenant = $request->user()->tenant;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'main_category' => ['nullable', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:500'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'kecamatan' => ['nullable', 'string', 'max:80'],
            'desa' => ['nullable', 'string', 'max:80'],
        ], [
            'name.required' => 'Nama usaha wajib diisi.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'lat.required' => 'Tentukan titik lokasi lapak di peta.',
            'lng.required' => 'Tentukan titik lokasi lapak di peta.',
        ]);

        // Hasil pencarian kecamatan/desa yang kosong tidak boleh menimpa data lama.
        $data['kecamatan'] = $data['kecamatan'] ?: $tenant->kecamatan;
        $data['desa'] = $data['desa'] ?: $tenant->desa;

        $tenant->update($data);

        return redirect()->to(route('penjual.dashboard').'#profil')->with('ok', 'Profil lapak berhasil disimpan.');
    }
}
