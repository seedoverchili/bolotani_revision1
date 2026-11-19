<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.public')]
class Marketplace extends Component
{
    use WithPagination;

    public const PER_PAGE = 5;

    #[Url(as: 'cari')]
    public string $q = '';

    #[Url(as: 'kategori')]
    public string $category = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        $this->category = array_key_exists($category, Product::CATEGORIES) ? $category : '';
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->with(['tenant', 'images'])
            ->approved()
            ->whereHas('tenant', fn($t) => $t->where('status', 'approved'))
            ->when($this->category, fn($q) => $q->where(fn($w) => $w
                ->where('category', $this->category)
                ->orWhereJsonContains('categories', $this->category)))
            ->when(trim($this->q) !== '', function ($q) {
                $term = '%' . trim($this->q) . '%';
                $q->where(fn($w) => $w
                    ->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('tags', 'like', $term)
                    ->orWhereHas('tenant', fn($t) => $t->where('name', 'like', $term)));
            })
            ->orderBy('id')
            ->paginate(self::PER_PAGE);

        return view('livewire.marketplace', ['products' => $products]);
    }
}
