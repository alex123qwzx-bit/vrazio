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
    public string $priceRange = '';
    public ?float $minPrice = null;
    public ?float $maxPrice = null;
    public string $sortBy = 'latest';
    public bool $certifiedOnly = false;

    protected $queryString = [
        'category' => ['except' => ''],
        'metalType' => ['except' => ''],
        'search' => ['except' => ''],
        'priceRange' => ['except' => ''],
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

    public function updatedPriceRange(string $value): void
    {
        $this->resetPage();
        match ($value) {
            'under_50k' => [$this->minPrice = null, $this->maxPrice = 50000.0],
            '50k_100k' => [$this->minPrice = 50000.0, $this->maxPrice = 100000.0],
            '100k_250k' => [$this->minPrice = 100000.0, $this->maxPrice = 250000.0],
            'above_250k' => [$this->minPrice = 250000.0, $this->maxPrice = null],
            default => [$this->minPrice = null, $this->maxPrice = null],
        };
    }

    public function resetFilters(): void
    {
        $this->reset(['category', 'metalType', 'search', 'minPrice', 'maxPrice', 'priceRange', 'sortBy', 'certifiedOnly']);
        $this->resetPage();
    }

    public function render(): View
    {
        $query = Product::query()->inStock();

        if ($this->category) {
            $query->byCategory($this->category);
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

        $categories = Product::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->pluck('category')
            ->sort()
            ->values();

        $metalTypes = Product::select('metal_type')->distinct()->pluck('metal_type');

        return view('livewire.product-catalog', [
            'products' => $products,
            'categories' => $categories,
            'metalTypes' => $metalTypes,
        ]);
    }
}
