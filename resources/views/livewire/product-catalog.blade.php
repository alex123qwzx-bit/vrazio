<div>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- Filters Sidebar --}}
        <aside class="lg:col-span-1">
            <div class="bg-white border border-[#D4AF37]/20 p-6 shadow-sm sticky top-28 space-y-6">
                {{-- Live Search --}}
                <div>
                    <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-2">Search Vault</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Type title, SKU, or gem..." 
                           class="w-full text-xs px-3 py-2 border border-gray-200 focus:border-[#D4AF37] focus:outline-none">
                </div>

                {{-- Category Filter --}}
                <div class="border-t border-gray-100 pt-5">
                    <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-3">Category</label>
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#0E261C]">
                            <input type="radio" wire:model.live="category" value="" class="text-[#0E261C] mr-2 focus:ring-[#D4AF37]">
                            All Categories
                        </label>
                        @php
                            $categoryLabels = [
                                'rings' => 'Rings',
                                'necklaces' => 'Necklaces',
                                'bracelets' => 'Bracelets',
                                'earrings' => 'Earrings',
                                'solitaires' => 'Solitaires',
                                'jewelry' => 'Fine Jewelry',
                                'clothing' => 'Haute Couture & Clothes',
                                'couture' => 'Couture Creations',
                                'watches' => 'Timepieces & Watches',
                                'footwear' => 'Bespoke Footwear',
                            ];
                        @endphp
                        @foreach($categories as $cat)
                            <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#0E261C]">
                                <input type="radio" wire:model.live="category" value="{{ $cat }}" class="text-[#0E261C] mr-2 focus:ring-[#D4AF37]">
                                {{ $categoryLabels[$cat] ?? ucfirst($cat) }}
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Price Segment Filter --}}
                <div class="border-t border-gray-100 pt-5">
                    <label class="block text-xs uppercase tracking-widest text-[#0E261C] font-bold mb-3">Price Segment</label>
                    <div class="space-y-2">
                        <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#0E261C]">
                            <input type="radio" wire:model.live="priceRange" value="" class="text-[#0E261C] mr-2 focus:ring-[#D4AF37]">
                            All Prices
                        </label>
                        <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#0E261C]">
                            <input type="radio" wire:model.live="priceRange" value="under_50k" class="text-[#0E261C] mr-2 focus:ring-[#D4AF37]">
                            Under ₹50,000
                        </label>
                        <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#0E261C]">
                            <input type="radio" wire:model.live="priceRange" value="50k_100k" class="text-[#0E261C] mr-2 focus:ring-[#D4AF37]">
                            ₹50,000 – ₹1,00,000
                        </label>
                        <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#0E261C]">
                            <input type="radio" wire:model.live="priceRange" value="100k_250k" class="text-[#0E261C] mr-2 focus:ring-[#D4AF37]">
                            ₹1,00,000 – ₹2,50,000
                        </label>
                        <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#0E261C]">
                            <input type="radio" wire:model.live="priceRange" value="above_250k" class="text-[#0E261C] mr-2 focus:ring-[#D4AF37]">
                            Above ₹2,50,000
                        </label>
                    </div>
                </div>

                {{-- Certification --}}
                <div class="border-t border-gray-100 pt-5">
                    <label class="flex items-center text-xs text-[#0E261C]/80 cursor-pointer hover:text-[#0E261C]">
                        <input type="checkbox" wire:model.live="certifiedOnly" class="rounded text-[#0E261C] mr-2">
                        GIA / IGI Certified Only
                    </label>
                </div>

                <div class="pt-4">
                    <button type="button" wire:click="resetFilters" class="w-full py-2.5 border border-gray-300 text-[11px] uppercase tracking-wider text-gray-600 hover:bg-gray-50">
                        Reset Filters
                    </button>
                </div>
            </div>
        </aside>

        {{-- Catalog Products --}}
        <main class="lg:col-span-3">
            <div class="flex items-center justify-between bg-white border border-[#D4AF37]/20 px-6 py-4 mb-6 shadow-sm">
                <span class="text-xs text-gray-600 font-medium">
                    Showing <strong class="text-[#0E261C]">{{ $products->total() }}</strong> pieces
                </span>

                <div class="flex items-center gap-2">
                    <label class="text-xs uppercase tracking-wider text-gray-500">Sort:</label>
                    <select wire:model.live="sortBy" class="text-xs py-1.5 px-3 border border-gray-200 focus:border-[#D4AF37] focus:outline-none bg-white">
                        <option value="latest">Newest Curations</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="title_asc">Title: A to Z</option>
                    </select>
                </div>
            </div>

            <div wire:loading.class="opacity-50" class="transition-opacity">
                @if($products->isEmpty())
                    <div class="bg-white border border-[#D4AF37]/20 p-12 text-center shadow-sm">
                        <h3 class="font-display text-lg font-bold text-[#0E261C] mb-2">No Matching Jewels in Vault</h3>
                        <p class="text-xs text-gray-500">Adjust search parameters to locate desired pieces.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>
