<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    public string $category = '';
    public string $metalType = '';
    public string $search = '';
    public ?float $minPrice = null;
    public ?float $maxPrice = null;
    public string $sortBy = 'latest';
    public bool $certifiedOnly = false;

    protected $queryString = [
        'category' => ['except' => ''],
        'metalType' => ['except' => ''],
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'latest'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingMetalType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['category', 'metalType', 'search', 'minPrice', 'maxPrice', 'sortBy', 'certifiedOnly']);
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Product::query()->inStock();

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->metalType) {
            $query->where('metal_type', $this->metalType);
        }

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->minPrice !== null) {
            $query->where('price', '>=', $this->minPrice);
        }

        if ($this->maxPrice !== null) {
            $query->where('price', '<=', $this->maxPrice);
        }

        if ($this->certifiedOnly) {
            $query->whereNotNull('certification');
        }

        match ($this->sortBy) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'title_asc' => $query->orderBy('title', 'asc'),
            default => $query->latest(),
        };

        $products = $query->paginate(12);

        $metalTypes = Product::select('metal_type')->distinct()->pluck('metal_type');

        return view('livewire.product-catalog', [
            'products' => $products,
            'metalTypes' => $metalTypes,
        ]);
    }
}
