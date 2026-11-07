@props(['links' => []])

<nav class="py-4 text-xs font-body tracking-wider uppercase" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-gray-500">
        <li>
            <a href="{{ route('home') }}" class="hover:text-[#D4AF37] transition-colors">Home</a>
        </li>
        @foreach($links as $label => $url)
            <li class="flex items-center space-x-2">
                <span class="text-gray-400">/</span>
                @if($url && ! $loop->last)
                    <a href="{{ $url }}" class="hover:text-[#D4AF37] transition-colors">{{ $label }}</a>
                @else
                    <span class="text-[#0E261C] font-semibold">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
