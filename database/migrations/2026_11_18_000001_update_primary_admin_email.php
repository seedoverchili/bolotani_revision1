<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $oldEmail = 'admin@gmail.com';
            $newEmail = 'mohridwanramadan@gmail.com';
            $changed = DB::table('users')
                ->where('email', $oldEmail)
                ->where('role', 'admin')
                ->update(['email' => $newEmail]);
            // 2.17 — Bagian 2 · Masuk admin — Ganti email akun lama. Fungsi: Memperbarui akun admin yang sama dengan mempertahankan ID dan hash sandi. Indeks unik mencegah penimpaan bila alamat tujuan sudah dipakai.

            if ($changed) {
                DB::table('password_reset_tokens')->whereIn('email', [$oldEmail, $newEmail])->delete();
            }
            // 2.18 — Bagian 2 · Masuk admin — Batalkan tautan reset sebelumnya. Fungsi: Setelah alamat berubah, admin meminta tautan baru untuk email yang baru.
        });
    }

    public function down(): void
    {
        // Perubahan identitas berlaku satu arah. Akun yang sejak awal memakai email baru tidak boleh diubah saat rollback.
    }
};
