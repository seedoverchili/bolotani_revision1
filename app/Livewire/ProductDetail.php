<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ProductDetail extends Component
{
    public Product $product;

    public int $active = 0;

    public function mount(Product $product): void
    {
        abort_unless($product->tenant->isApproved(), 404);
        // Produk yang belum disetujui hanya bisa dilihat pemilik dan admin.
        abort_unless($product->isApproved() || auth()->user()?->isAdmin() || auth()->id() === $product->tenant->user_id, 404);

        $this->product = $product->load(['tenant', 'images']);
    }

    public function select(int $index): void
    {
        $this->active = max(0, min($index, $this->product->images->count() - 1));
    }

    public function render()
    {
        $related = Product::query()
            ->with(['tenant', 'images'])
            ->where('tenant_id', '!=', $this->product->tenant_id)
            ->approved()
            ->whereHas('tenant', fn($t) => $t->where('status', 'approved'))
            ->whereIn('category', ['produk_pertanian', 'hasil_pertanian'])
            ->orderByDesc('id')
            ->take(3)
            ->get()
            ->sortBy('id')
            ->values();

        return view('livewire.product-detail', ['related' => $related]);
    }
}
