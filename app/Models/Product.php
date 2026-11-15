<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const CATEGORIES = [
        'produk_pertanian' => 'Produk Pertanian',
        'hasil_pertanian' => 'Hasil Pertanian',
        'produk_olahan' => 'Produk Olahan',
        'alat_pertanian' => 'Alat Pertanian',
    ];

    /** Tag format dasar yang boleh ada di deskripsi. */
    private const ALLOWED_TAGS = ['b', 'strong', 'i', 'em', 'ul', 'ol', 'li', 'br', 'p', 'div'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['categories' => 'array'];
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function coverUrl(): string
    {
        $first = $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();

        return $first ? asset($first->path) : asset('images/products/kaktus-mini.jpg');
    }

    /** Deskripsi aman untuk ditampilkan sebagai HTML sederhana. */
    public function descriptionHtml(): string
    {
        return self::cleanHtml((string) $this->description);
    }

    /** Deskripsi sebagai teks polos untuk kartu ringkas. */
    public function descriptionText(): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags(str_replace(['</li>', '<br>', '</p>'], ' ', (string) $this->description))));
    }

    public function priceFormatted(): string
    {
        return 'Rp '.number_format($this->price, 0, ',', '.');
    }

    /**
     * Hanya tag format dasar, tanpa atribut. Tag lain dibuang, dan "<" yang
     * bukan tag diizinkan diubah jadi teks biasa agar tidak bisa disusupi.
     */
    public static function cleanHtml(string $html): string
    {
        $allowed = implode('|', self::ALLOWED_TAGS);

        do {
            $before = $html;
            $html = strip_tags($html, self::ALLOWED_TAGS);
        } while ($html !== $before);

        $html = preg_replace_callback(
            '/<\s*(\/?)\s*('.$allowed.')\b[^>]*>/i',
            fn ($m) => '<'.$m[1].strtolower($m[2]).'>',
            $html
        );

        $html = preg_replace('/<(?!\/?(?:'.$allowed.')>)/i', '&lt;', $html);

        return trim($html);
    }
}