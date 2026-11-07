<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CartCounter extends Component
{
    protected $listeners = ['cartUpdated' => '$refresh'];

    public function render(CartService $cartService): View
    {
        $count = $cartService->getItemCount();

        return view('livewire.cart-counter', compact('count'));
    }
}
