<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function edit(Request $request)
    {
        return view('penjual.foto', ['tenant' => $request->user()->tenant]);
    }

    public function update(Request $request)
    {
        $tenant = $request->user()->tenant;

        $data = $request->validate([
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'cover_pos' => ['nullable', 'integer', 'in:0,50,100'],
        ], [
            'cover.image' => 'Foto sampul harus berupa gambar JPG atau PNG.',
            'cover.max' => 'Ukuran foto sampul maksimal 5 MB.',
            'avatar.image' => 'Foto profil harus berupa gambar JPG atau PNG.',
            'avatar.max' => 'Ukuran foto profil maksimal 2 MB.',
        ]);

        $updates = [];
        foreach (['cover', 'avatar'] as $field) {
            if ($request->hasFile($field)) {
                $this->deleteOld($tenant->{$field});
                $updates[$field] = 'storage/'.$request->file($field)->store('tenants', 'public');
            }
        }
        if (isset($data['cover_pos'])) {
            $updates['cover_pos'] = $data['cover_pos'];
        }

        $tenant->update($updates);

        return redirect()->route('penjual.dashboard')->with('ok', 'Foto lapak berhasil diperbarui.');
    }

    private function deleteOld(?string $path): void
    {
        if ($path && str_starts_with($path, 'storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('storage/')));
        }
    }
}
