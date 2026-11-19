<?php

namespace App\Livewire;

use App\Models\Tenant;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.public')]
class TenantProfile extends Component
{
    public Tenant $tenant;

    #[Url(as: 'cari')]
    public string $q = '';

    #[Url(as: 'kelompok')]
    public string $group = '';

    public function mount(Tenant $tenant): void
    {
        abort_unless($tenant->isApproved() || auth()->user()?->isAdmin() || auth()->id() === $tenant->user_id, 404);

        $this->tenant = $tenant;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    public function render()
    {
        $canSeeAll = auth()->user()?->isAdmin() || auth()->id() === $this->tenant->user_id;
        $all = $this->tenant->products()->with('images')
            ->when(! $canSeeAll, fn($q) => $q->approved())
            ->orderBy('id')->get();

        $groups = $all->groupBy('group')->map->count()->filter(fn($n, $g) => filled($g));

        $term = mb_strtolower(trim($this->q));
        $products = $all
            ->when($this->group !== '', fn($c) => $c->where('group', $this->group))
            ->when($term !== '', fn($c) => $c->filter(fn($p) => str_contains(mb_strtolower($p->name . ' ' . $p->tags . ' ' . $p->description), $term)));

        return view('livewire.tenant-profile', [
            'total' => $all->count(),
            'groups' => $groups,
            'products' => $products->values(),
            'isOwner' => auth()->id() === $this->tenant->user_id,
        ]);
    }
}
