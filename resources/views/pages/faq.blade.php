@extends('layouts.app')

@section('title', 'Frequently Asked Questions')
@section('meta_description', 'Explore answers to questions about VRAZIO fine jewellery: BIS hallmarking, GIA certifications, insured doorstep transit, warranty claims, and custom design commissions.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-6">
        <x-breadcrumbs :links="['Clientele Concierge' => '', 'FAQ' => '']" />

        @include('sections.faq')
    </div>
</div>
@endsection
