<?php

namespace App\Http\Controllers\Penjual;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public const UNITS = ['buah', 'batang', 'polybag', 'pohon', 'kg', 'gram', 'liter', 'botol', 'pack', 'pouch', 'ikat', 'karung', 'unit'];

    public function create(Request $request)
    {
        abort_unless($request->user()->tenant, 403);

        // Satuan bawaan + satuan yang pernah dipakai di katalog, tanpa duplikat.
        $units = collect(self::UNITS)
            ->merge(Product::query()->distinct()->pluck('unit'))
            ->map(fn ($u) => Str::lower(trim($u)))
            ->filter()->unique()->values()->all();

        return view('penjual.produk-tambah', ['units' => $units]);
    }

    public function store(Request $request)
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        // Dibersihkan SEBELUM validasi, sehingga old('description') di form juga sudah aman.
        $description = Product::cleanHtml((string) $request->input('description', ''));
        $plain = str_replace("\xC2\xA0", ' ', html_entity_decode(strip_tags($description)));
        if (! preg_match('/\S/u', $plain)) {
            $description = ''; // editor kosong, misalnya hanya <div><br></div>
        }

        $request->merge([
            'description' => $description,
            // "15.000" atau "Rp 15.000" diterima sebagai 15000
            'price' => str_ireplace(['Rp', '.', ' '], '', (string) $request->input('price', '')),
        ]);

        $data = $request->validate([
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => [Rule::in(array_keys(Product::CATEGORIES))],
            'name' => ['required', 'string', 'max:100'],
            'tags' => ['nullable', 'string', 'max:160'],
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'price' => ['required', 'integer', 'min:100', 'max:2000000000'],
            'unit' => ['required', 'string', 'max:40', 'regex:/^[\pL\pN\s\/().,-]+$/u'],
            'description' => ['required', 'string', 'max:6000'],
        ], [
            'categories.required' => 'Pilih minimal satu pilar kategori.',
            'categories.min' => 'Pilih minimal satu pilar kategori.',
            'name.required' => 'Nama komoditas wajib diisi.',
            'name.max' => 'Nama komoditas maksimal 100 karakter.',
            'photos.required' => 'Unggah minimal 1 foto komoditas.',
            'photos.min' => 'Unggah minimal 1 foto komoditas.',
            'photos.max' => 'Maksimal 5 foto.',
            'photos.*.image' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photos.*.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 5 MB.',
            'photos.*.uploaded' => 'Foto gagal diunggah. Ukuran file mungkin terlalu besar.',
            'price.required' => 'Harga jual wajib diisi.',
            'price.integer' => 'Harga harus berupa angka.',
            'price.min' => 'Harga minimal Rp 100.',
            'unit.required' => 'Pilih satuan jual.',
            'unit.max' => 'Satuan maksimal 40 karakter.',
            'unit.regex' => 'Satuan hanya boleh berisi huruf, angka, dan tanda baca sederhana.',
            'description.required' => 'Deskripsi komoditas wajib diisi.',
            'description.max' => 'Deskripsi terlalu panjang.',
        ]);

        $categories = array_values(array_unique($data['categories']));
        $stored = [];

        try {
            DB::transaction(function () use ($data, $categories, $request, $tenant, &$stored) {
                $product = $tenant->products()->create([
                    'category' => $categories[0],
                    'categories' => $categories,
                    'name' => $data['name'],
                    'slug' => $this->uniqueSlug($data['name']),
                    'sku' => null, // diisi di bawah dari ID produk, supaya pasti unik
                    'subcategory' => Str::of($data['tags'] ?? '')->before(',')->trim()->limit(40, '')->toString() ?: null,
                    'tags' => $data['tags'] ?? null,
                    'price' => $data['price'],
                    'unit' => Str::lower(trim($data['unit'])),
                    'stock' => 100,
                    'status' => 'pending',
                    'description' => $data['description'],
                ]);

                $product->update(['sku' => $this->makeSku($tenant->name, $categories[0], $product->id)]);

                foreach (array_values($request->file('photos')) as $i => $file) {
                    $path = $file->store('products', 'public');
                    $stored[] = $path;

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => 'storage/'.$path,
                        'label' => ['Foto Utama', 'Foto 2', 'Foto 3', 'Foto 4', 'Foto 5'][$i] ?? null,
                        'sort' => $i,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            // Transaksi dibatalkan, jadi file yang sudah terlanjur tersimpan ikut dibuang.
            foreach ($stored as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        return redirect()->route('penjual.produk.create')->with('saved', true);
    }

    /** Stok hanya dua keadaan: Ready (100) atau Habis (0). */
    public function toggleStock(Request $request, Product $product)
    {
        $this->authorizeOwner($request, $product);

        $product->update(['stock' => $product->stock > 0 ? 0 : 100]);

        return redirect()->to(route('penjual.dashboard').'#katalog');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeOwner($request, $product);

        $paths = $product->images->pluck('path')->all();

        $product->delete(); // baris product_images ikut terhapus (cascade)

        foreach ($paths as $path) {
            if (str_starts_with($path, 'storage/')) {
                Storage::disk('public')->delete(substr($path, strlen('storage/')));
            }
        }

        return redirect()->to(route('penjual.dashboard').'#katalog')->with('ok', 'Produk dihapus.');
    }

    /** Pedagang hanya boleh mengubah/menghapus produk miliknya. */
    private function authorizeOwner(Request $request, Product $product): void
    {
        $tenantId = $request->user()->tenant?->id;

        abort_unless($tenantId !== null && $product->tenant_id === $tenantId, 403);
    }

    private function makeSku(string $tenantName, string $category, int $productId): string
    {
        $prefix = strtoupper(Str::substr(Str::slug($tenantName, ''), 0, 3)) ?: 'TKO';

        return $prefix.'-'.strtoupper(Str::substr($category, 0, 3)).'-'.str_pad((string) $productId, 4, '0', STR_PAD_LEFT);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'produk';
        $slug = $base;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }
}