<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class SetujuiProduk extends Command
{
    protected $signature = 'produk:setujui {id? : ID produk yang disetujui} {--semua : Setujui semua produk yang masih pending}';

    protected $description = 'Setujui produk pedagang (pengganti sementara dashboard admin)';

    public function handle(): int
    {
        if ($this->option('semua')) {
            $jumlah = Product::where('status', 'pending')->update(['status' => 'approved']);
            $this->info("{$jumlah} produk disetujui.");

            return self::SUCCESS;
        }

        if ($id = $this->argument('id')) {
            $product = Product::find($id);

            if (! $product) {
                $this->error("Produk #{$id} tidak ditemukan.");

                return self::FAILURE;
            }

            $product->update(['status' => 'approved']);
            $this->info("Produk #{$product->id} \"{$product->name}\" disetujui.");

            return self::SUCCESS;
        }

        $pending = Product::with('tenant:id,name')->where('status', 'pending')->orderBy('id')->get();

        if ($pending->isEmpty()) {
            $this->info('Tidak ada produk yang menunggu persetujuan.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'SKU', 'Produk', 'Toko', 'Dibuat'],
            $pending->map(fn ($p) => [$p->id, $p->sku, $p->name, $p->tenant?->name, $p->created_at?->format('d/m/Y H:i')])->all()
        );
        $this->line('Setujui satu: php artisan produk:setujui <ID>   |   semuanya: php artisan produk:setujui --semua');

        return self::SUCCESS;
    }
}