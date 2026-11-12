<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Banner highlighting Draft vs Published state --}}
        <div class="p-4 rounded-lg bg-emerald-950/20 border border-emerald-500/30 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400">
                    <x-filament::icon icon="heroicon-o-sparkles" class="w-5 h-5 text-emerald-400" />
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">VRAZIO Landing Page Management Console</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Edit sections, sliders, categories, and SEO below. Use <strong>Save Draft</strong> to stage changes, or <strong>Publish Changes</strong> to push to the live luxury storefront.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <x-filament::button
                    tag="a"
                    href="{{ route('home', ['preview' => 'draft']) }}"
                    target="_blank"
                    size="sm"
                    color="gray"
                    icon="heroicon-o-eye"
                >
                    Live Preview
                </x-filament::button>
            </div>
        </div>

        <form wire:submit="publish">
            {{ $this->form }}

            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-800 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-filament::button
                        type="button"
                        wire:click="saveDraft"
                        color="info"
                        icon="heroicon-o-document-check"
                    >
                        Save Draft
                    </x-filament::button>

                    <x-filament::button
                        type="button"
                        tag="a"
                        href="{{ route('home', ['preview' => 'draft']) }}"
                        target="_blank"
                        color="gray"
                        icon="heroicon-o-eye"
                    >
                        Preview Draft Storefront
                    </x-filament::button>
                </div>

                <div class="flex items-center gap-3">
                    <x-filament::button
                        type="button"
                        wire:click="publish"
                        color="warning"
                        icon="heroicon-o-check-badge"
                        size="lg"
                    >
                        Publish Changes to Live Storefront
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>
</x-filament-panels::page>
