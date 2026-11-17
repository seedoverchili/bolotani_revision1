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

    public function create()
    {
        // Satuan bawaan + satuan yang pernah ditambahkan pedagang lain, tanpa duplikat.
        $units = collect(self::UNITS)
            ->merge(Product::query()->distinct()->pluck('unit'))
            ->map(fn ($u) => Str::lower(trim($u)))
            ->filter()->unique()->values()->all();

        return view('penjual.produk-tambah', ['units' => $units]);
    }

    public function store(Request $request)
    {
        $tenant = $request->user()->tenant;

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
            'photos.max' => 'Maksimal 5 foto.',
            'photos.*.image' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 5 MB.',
            'price.required' => 'Harga jual wajib diisi.',
            'price.integer' => 'Harga harus berupa angka.',
            'unit.required' => 'Pilih satuan jual.',
            'unit.max' => 'Satuan maksimal 40 karakter.',
            'unit.regex' => 'Satuan hanya boleh berisi huruf, angka, dan tanda baca sederhana.',
            'description.required' => 'Deskripsi komoditas wajib diisi.',
        ]);

        DB::transaction(function () use ($data, $request, $tenant) {
            $count = $tenant->products()->count() + 1;
            $prefix = strtoupper(Str::substr(Str::slug($tenant->name, ''), 0, 3));

            $product = $tenant->products()->create([
                'category' => $data['categories'][0],
                'categories' => array_values(array_unique($data['categories'])),
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'sku' => $prefix.'-'.strtoupper(Str::substr($data['categories'][0], 0, 3)).'-'.str_pad((string) $count, 3, '0', STR_PAD_LEFT),
                'subcategory' => Str::of($data['tags'] ?? '')->before(',')->trim()->limit(40, '')->toString() ?: null,
                'tags' => $data['tags'] ?? null,
                'price' => $data['price'],
                'unit' => Str::lower(trim($data['unit'])),
                'stock' => 100,
                'status' => 'pending',
                'description' => self::cleanHtml($data['description']),
            ]);

            foreach ($request->file('photos') as $i => $file) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => 'storage/'.$file->store('products', 'public'),
                    'label' => ['Foto Utama', 'Foto 2', 'Foto 3', 'Foto 4', 'Foto 5'][$i] ?? null,
                    'sort' => $i,
                ]);
            }
        });

        return redirect()->route('penjual.produk.create')->with('saved', true);
    }

    public function toggleStock(Request $request, Product $product)
    {
        $this->authorizeOwner($request, $product);
        $product->update(['stock' => $product->stock > 0 ? 0 : 100]);

        return redirect()->to(route('penjual.dashboard').'#katalog');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeOwner($request, $product);

        foreach ($product->images as $image) {
            if (str_starts_with($image->path, 'storage/')) {
                Storage::disk('public')->delete(substr($image->path, strlen('storage/')));
            }
        }
        $product->delete();

        return redirect()->to(route('penjual.dashboard').'#katalog')->with('ok', 'Produk dihapus.');
    }

    private function authorizeOwner(Request $request, Product $product): void
    {
        abort_unless($product->tenant_id === $request->user()->tenant?->id, 403);
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name) ?: 'produk';
        while (Product::where('slug', $slug)->exists()) {
            $slug = Str::slug($name).'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }

    /** Deskripsi dari editor sederhana: hanya tag format dasar, tanpa atribut. */
    public static function cleanHtml(string $html): string
    {
        $html = strip_tags($html, '<b><strong><i><em><ul><ol><li><br><p><div>');

        return trim(preg_replace('/<(\w+)\s[^>]*>/', '<$1>', $html));
    }
}
