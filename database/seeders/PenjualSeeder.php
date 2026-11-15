<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Akun contoh untuk pengujian. JANGAN dijalankan di production. */
class PenjualSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('PenjualSeeder dilewati di production.');

            return;
        }

        User::updateOrCreate(
            ['email' => 'admin@bolotani.test'],
            ['name' => 'Admin Bolo Tani', 'password' => 'Admin12345', 'role' => 'admin', 'email_verified_at' => now()]
        );

        $penjual = User::updateOrCreate(
            ['email' => 'penjual@bolotani.test'],
            ['name' => 'Penjual Contoh', 'password' => 'Penjual12345', 'role' => 'penjual', 'email_verified_at' => now()]
        );

        Tenant::updateOrCreate(['user_id' => $penjual->id], [
            'code' => 'TKT-2026-0001',
            'name' => 'Tani Makmur Contoh',
            'slug' => 'tani-makmur-contoh',
            'owner_name' => 'Penjual Contoh',
            'whatsapp' => '081234567890',
            'email' => 'penjual@bolotani.test',
            'categories' => ['produk_pertanian'],
            'main_category' => 'Produk Pertanian',
            'kecamatan' => 'Genteng',
            'desa' => 'Genteng Kulon',
            'address' => 'Jl. Raya Genteng No. 1, Banyuwangi',
            'lat' => -8.3650000,
            'lng' => 114.1470000,
            'description' => 'Lapak contoh untuk pengujian modul pedagang.',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }
}