@extends('layouts.app')

@section('title', $homepageConfig['seo']['meta_title'] ?? 'Haute Joaillerie & Master Atelier')
@section('meta_description', $homepageConfig['seo']['meta_description'] ?? 'Welcome to VRAZIO Haute Joaillerie. Discover handcrafted fine jewellery, BIS hallmarked gold, GIA certified diamond solitaires, and royal bridal couture.')
@section('meta_keywords', $homepageConfig['seo']['meta_keywords'] ?? 'VRAZIO, fine jewellery, diamond ring, haute joaillerie, BIS hallmarked gold, GIA certified diamond')

@if(!empty($homepageConfig['seo']['og_image']))
    @section('og_image', Str::startsWith($homepageConfig['seo']['og_image'], ['http://', 'https://', '/']) ? $homepageConfig['seo']['og_image'] : asset('storage/' . $homepageConfig['seo']['og_image']))
@endif

@section('content')

{{-- Draft Preview Mode Bar for Administrators --}}
@if(!empty($isPreview))
    <div class="sticky top-0 z-50 bg-[#9B1B30] text-white px-6 py-2.5 shadow-lg flex items-center justify-between text-xs tracking-widest uppercase font-semibold">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
            <span>Atelier Draft Preview Active — Changes are not yet published to public patrons</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ url('/admin/homepage-manager') }}" class="underline hover:text-amber-300">
                Back to Homepage Manager &rarr;
            </a>
            <a href="{{ route('home') }}" class="px-2.5 py-1 bg-black/30 hover:bg-black/50 text-white rounded-xs">
                Exit Preview
            </a>
        </div>
    </div>
@endif

@php
    $orderedSections = $homepageConfig['sections_order'] ?? [
        ['id' => 'hero', 'is_active' => true],
        ['id' => 'trust_strip', 'is_active' => true],
        ['id' => 'categories', 'is_active' => true],
        ['id' => 'featured_products', 'is_active' => true],
        ['id' => 'heritage', 'is_active' => true],
        ['id' => 'gallery', 'is_active' => true],
        ['id' => 'testimonials', 'is_active' => true],
        ['id' => 'faq', 'is_active' => true],
    ];
@endphp

@foreach($orderedSections as $sectionItem)
    @php
        $sectionId = is_array($sectionItem) ? ($sectionItem['id'] ?? '') : (string) $sectionItem;
        $isActive = is_array($sectionItem) ? ($sectionItem['is_active'] ?? true) : true;
    @endphp

    @if($isActive)
        @switch($sectionId)
            @case('hero')
                @include('sections.hero')
                @break

            @case('trust_strip')
                @include('sections.trust_strip')
                @break

            @case('categories')
                @include('sections.categories')
                @break

            @case('featured_products')
                @include('sections.featured_products')
                @break

            @case('heritage')
                @include('sections.heritage')
                @break

            @case('gallery')
            @case('showcase')
                @include('sections.showcase')
                @break

            @case('testimonials')
                @include('sections.testimonials')
                @break

            @case('faq')
                @include('sections.faq')
                @break
        @endswitch
    @endif
@endforeach

@endsection
