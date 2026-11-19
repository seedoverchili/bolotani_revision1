<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'lat' => 'float',
            'lng' => 'float',
            'approved_at' => 'datetime',
            'email_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar ? asset($this->avatar) : null;
    }

    public function avatarLargeUrl(): ?string
    {
        if (! $this->avatar) {
            return null;
        }
        $large = preg_replace('/(\.\w+)$/', '-lg$1', $this->avatar);

        return asset(file_exists(public_path($large)) ? $large : $this->avatar);
    }

    public function coverUrl(): string
    {
        return asset($this->cover ?: 'images/tenants/rohmad-cover.jpg');
    }

    public function waNumber(): string
    {
        $n = preg_replace('/\D+/', '', $this->whatsapp);

        return str_starts_with($n, '0') ? '62'.substr($n, 1) : $n;
    }

    public function waLink(): string
    {
        return 'https://wa.me/'.$this->waNumber();
    }

    /** "Desa X, Kec. Y" dari hasil peta; kosong bila lokasi tidak terdeteksi. */
    public function areaLabel(): string
    {
        return collect([$this->desa ? 'Desa '.$this->desa : null, $this->kecamatan ? 'Kec. '.$this->kecamatan : null])->filter()->implode(', ');
    }

    public function mapsLink(): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination='.$this->lat.','.$this->lng;
    }
}
