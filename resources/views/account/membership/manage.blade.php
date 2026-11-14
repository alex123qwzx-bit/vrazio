@extends('layouts.app')

@section('title', 'Manage Membership | VRAZIO PRIVÉ CLUB')
@section('meta_description', 'Manage your VRAZIO PRIVÉ CLUB membership, review benefits, request tier upgrade or cancellation.')

@push('head')
<script>
    function membershipManager() {
        return {
            cancelModal: false,
            upgradeModal: false,
            higherPlans: @json($higherPlans),
            selectedUpgradeId: {{ $higherPlans->first()?->id ?? 'null' }},
            cancelReason: '',
            cancelNotes: '',
            requestRefund: false,
            submitting: false,
            get selectedUpgrade() {
                if (!this.selectedUpgradeId || !this.higherPlans) return null;
                return this.higherPlans.find(p => p.id === this.selectedUpgradeId) || null;
            },
            openUpgradeModal(planId) {
                this.selectedUpgradeId = planId;
                this.submitting = false;
                this.upgradeModal = true;
            },
            openCancelModal() {
                this.submitting = false;
                this.cancelModal = true;
            }
        };
    }
</script>
@endpush

@section('content')
<div x-data="membershipManager()" class="bg-[#FAF8F5] min-h-screen py-10 md:py-14">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumbs :links="[
            'Home' => route('home'),
            'My Account' => route('account'),
            'Manage Membership' => ''
        ]" />

        {{-- Notifications --}}
        @if(session('success'))
            <div class="my-6 p-4 bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 text-xs sm:text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="my-6 p-4 bg-red-50 border-l-4 border-[#9B1B30] text-red-800 text-xs sm:text-sm shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#9B1B30] shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="my-6 p-4 bg-amber-50 border-l-4 border-[#D4AF37] text-amber-900 text-xs sm:text-sm shadow-xs">
                {{ session('info') }}
            </div>
        @endif

        {{-- Active Pending Request Notice Banner --}}
        @php
            $activeRequest = $membership->activeRequest();
        @endphp
        @if($activeRequest)
            <div class="my-6 p-4 sm:p-5 bg-amber-50/90 border border-[#D4AF37]/60 rounded-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#D4AF37]/20 text-[#0E261C] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#0E261C]">
                            Active Request Under Review: {{ $activeRequest->request_number }} ({{ ucfirst($activeRequest->type) }})
                        </h4>
                        <p class="text-xs text-gray-600 mt-0.5">
                            Status: <span class="font-bold text-amber-800">{{ $activeRequest->status_label }}</span> • Submitted on {{ $activeRequest->created_at->format('d M Y, h:i A') }}.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ $activeRequest->getWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#25D366] text-white text-xs font-semibold uppercase tracking-wider hover:bg-[#1EBE5D] transition-colors rounded-xs shadow-xs">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.102-.115.434-.506.549-.68.116-.173.232-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                        WhatsApp Inquiry
                    </a>
                </div>
            </div>
        @endif

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between my-8 gap-4 pb-6 border-b border-gray-200">
            <div>
                <span class="text-[11px] uppercase tracking-[0.25em] text-[#D4AF37] font-semibold">
                    Atelier Membership Dossier
                </span>
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-[#0E261C] mt-1">
                    Manage Membership
                </h1>
                <p class="text-xs text-gray-500 mt-1">
                    View active plan credentials, explore tier upgrades, or manage your subscription status.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('account') }}" 
                   class="px-4 py-2.5 bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-semibold uppercase tracking-wider rounded-xs transition-colors">
                    &larr; Back to Account
                </a>
                <a href="https://wa.me/918791227210?text={{ urlencode('Hello VRAZIO Atelier, I have an inquiry regarding my PRIVÉ Club Membership (' . $membership->member_id . ').') }}" 
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#25D366] text-white text-xs font-semibold uppercase tracking-wider hover:bg-[#1EBE5D] transition-colors rounded-xs shadow-xs">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.102-.115.434-.506.549-.68.116-.173.232-.144.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                    Contact Concierge
                </a>
            </div>
        </div>

        {{-- Main 2-Column Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Current Membership Dossier & Benefits --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Membership Pass Dossier Card --}}
                <div class="bg-white border-2 border-[#D4AF37] p-6 sm:p-8 rounded-2xl shadow-md">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4 mb-6">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-[0.25em] text-[#D4AF37]">
                                Current Membership Plan
                            </span>
                            <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#0E261C] mt-0.5">
                                {{ $membership->tier_name }}
                            </h2>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($membership->isActive())
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-bold text-xs uppercase tracking-wider rounded-full border border-emerald-300">
                                    Active Pass
                                </span>
                            @elseif($membership->status === 'cancelled')
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 font-bold text-xs uppercase tracking-wider rounded-full border border-gray-300">
                                    Cancelled
                                </span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 font-bold text-xs uppercase tracking-wider rounded-full border border-red-300">
                                    {{ ucfirst($membership->status) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Key Metric Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-3 bg-[#FAF8F5] p-4 rounded-xl border border-gray-100 text-xs">
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Member ID</span>
                            <span class="font-mono font-bold text-[#0E261C] text-sm">{{ $membership->member_id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Membership Fee</span>
                            <span class="font-mono font-bold text-[#0E261C] text-sm">₹{{ number_format((float) $membership->fee_paid, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Commencement</span>
                            <span class="font-semibold text-gray-800 text-xs">{{ $membership->starts_at?->format('d M Y') ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Next Renewal / Expiry</span>
                            <span class="font-semibold text-gray-800 text-xs">{{ $membership->expires_at?->format('d M Y') ?? 'N/A' }}</span>
                        </div>
                    </div>

                    {{-- Membership Benefits Checklist --}}
                    <div class="mt-6 pt-5 border-t border-gray-100">
                        <h3 class="text-xs uppercase font-bold tracking-[0.2em] text-[#0E261C] mb-3">
                            Included Atelier Privileges & Benefits
                        </h3>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-gray-700">
                            @if(!empty($plan?->benefits) && is_array($plan->benefits))
                                @foreach($plan->benefits as $benefit)
                                    <li class="flex items-start gap-2">
                                        <svg class="w-4 h-4 text-[#D4AF37] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $benefit }}</span>
                                    </li>
                                @endforeach
                            @else
                                <li class="text-gray-500 italic">Atelier Privé privileges active.</li>
                            @endif
                        </ul>
                    </div>

                    {{-- Actions Bar --}}
                    <div class="mt-8 pt-6 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('prive-club.verify', $membership->member_id) }}" target="_blank"
                               class="text-xs text-[#D4AF37] hover:underline font-bold uppercase tracking-wider flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                View QR Pass Docket
                            </a>
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5">
                            {{-- Cancel Membership Button --}}
                            @if($membership->canBeCancelled())
                                <button type="button" 
                                        @click="openCancelModal()"
                                        class="px-4 py-2.5 bg-white border border-[#9B1B30] text-[#9B1B30] hover:bg-[#9B1B30] hover:text-white text-xs font-semibold uppercase tracking-wider rounded-xs transition-colors cursor-pointer shadow-xs">
                                    Request Cancellation
                                </button>
                            @elseif($membership->isCancellationPending())
                                <span class="px-3 py-1.5 bg-amber-50 text-amber-900 border border-amber-300 text-xs font-semibold rounded-xs">
                                    Cancellation Pending Review
                                </span>
                            @endif

                            {{-- Upgrade Membership Button --}}
                            @if($higherPlans->isNotEmpty() && $membership->canBeUpgraded())
                                <button type="button" 
                                        @click="openUpgradeModal({{ $higherPlans->first()->id }})"
                                        class="px-5 py-2.5 bg-[#0E261C] text-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-bold uppercase tracking-wider rounded-xs transition-colors shadow-xs cursor-pointer">
                                    ✦ Upgrade Membership
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Upgrade Tier Comparison Grid (If higher tiers available) --}}
                @if($higherPlans->isNotEmpty())
                    <div class="bg-white border border-[#D4AF37]/30 p-6 sm:p-8 rounded-xl shadow-xs">
                        <div class="border-b border-gray-100 pb-3 mb-6">
                            <span class="text-[10px] uppercase font-bold tracking-[0.2em] text-[#D4AF37]">Available Upgrades</span>
                            <h3 class="font-display text-xl font-bold text-[#0E261C] mt-0.5">
                                Elevate Your Tier Privileges
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Upgrade to higher atelier tiers by paying only the pro-rated price difference.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($higherPlans as $higherPlan)
                                <div class="border border-[#D4AF37]/40 rounded-xl p-5 bg-[#FAF8F5] flex flex-col justify-between hover:shadow-md transition-shadow">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <h4 class="font-display text-lg font-bold text-[#0E261C]">{{ $higherPlan->name }}</h4>
                                                <p class="text-[11px] text-gray-500">{{ $higherPlan->tagline }}</p>
                                            </div>
                                            <span class="font-mono font-bold text-sm text-[#0E261C]">{{ $higherPlan->formatted_price }}</span>
                                        </div>

                                        <div class="my-3 p-2.5 bg-emerald-50 border border-emerald-200 rounded text-xs text-emerald-900">
                                            <span class="block font-bold">Upgrade Difference: {{ $higherPlan->formatted_difference }}</span>
                                            <span class="text-[10px] text-emerald-800">Only the price difference is charged. Validity resets to 12 months upon confirmation.</span>
                                        </div>

                                        <ul class="space-y-1.5 text-xs text-gray-600 mt-3">
                                            @foreach(array_slice($higherPlan->benefits ?? [], 0, 4) as $ben)
                                                <li class="flex items-start gap-1.5">
                                                    <span class="text-[#D4AF37] font-bold">✓</span>
                                                    <span class="text-[11px]">{{ $ben }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <div class="mt-5 pt-4 border-t border-gray-200">
                                        @if($membership->isUpgradePending())
                                            <button disabled class="w-full py-2 bg-gray-200 text-gray-500 text-xs font-bold uppercase tracking-wider rounded cursor-not-allowed">
                                                Upgrade Request Active
                                            </button>
                                        @else
                                            <button type="button" 
                                                    @click="openUpgradeModal({{ $higherPlan->id }})"
                                                    class="w-full py-2.5 bg-[#0E261C] text-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-bold uppercase tracking-wider rounded transition-colors cursor-pointer shadow-xs">
                                                Select {{ $higherPlan->name }} &rarr;
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Request History & Tracking Table --}}
                <div class="bg-white border border-[#D4AF37]/30 p-6 sm:p-8 rounded-xl shadow-xs">
                    <div class="border-b border-gray-100 pb-3 mb-6 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-[0.2em] text-[#D4AF37]">Audit Docket</span>
                            <h3 class="font-display text-xl font-bold text-[#0E261C] mt-0.5">
                                Membership Request History
                            </h3>
                        </div>
                        <span class="text-xs text-gray-400 font-mono">
                            {{ $requests->count() }} {{ $requests->count() === 1 ? 'Record' : 'Records' }}
                        </span>
                    </div>

                    @if($requests->isEmpty())
                        <div class="text-center py-8 text-gray-400 text-xs">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            No cancellation, refund, or upgrade requests recorded for your account.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-gray-200 text-gray-400 uppercase text-[10px] tracking-wider">
                                        <th class="py-2.5 pr-3">Docket #</th>
                                        <th class="py-2.5 px-3">Type</th>
                                        <th class="py-2.5 px-3">Submitted</th>
                                        <th class="py-2.5 px-3">Status</th>
                                        <th class="py-2.5 px-3">Concierge Response</th>
                                        <th class="py-2.5 pl-3 text-right">Support</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($requests as $req)
                                        <tr>
                                            <td class="py-3 pr-3 font-mono font-bold text-[#0E261C]">
                                                {{ $req->request_number }}
                                            </td>
                                            <td class="py-3 px-3">
                                                <span class="uppercase font-semibold text-[10px] px-2 py-0.5 rounded
                                                    {{ $req->type === 'upgrade' ? 'bg-blue-50 text-blue-800' : ($req->type === 'refund' ? 'bg-amber-50 text-amber-800' : 'bg-rose-50 text-rose-800') }}">
                                                    {{ $req->type }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 text-gray-600">
                                                {{ $req->created_at->format('d M Y') }}
                                            </td>
                                            <td class="py-3 px-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                                    {{ $req->status === 'approved' || $req->status === 'completed' || $req->status === 'refunded' ? 'bg-emerald-100 text-emerald-800' : ($req->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                                    {{ $req->status_label }}
                                                </span>
                                                @if($req->is_refund_requested && $req->refund_status)
                                                    <span class="block text-[9px] text-gray-500 mt-0.5">
                                                        Refund: <span class="font-bold">{{ $req->refund_status_label }}</span>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-gray-700 max-w-xs">
                                                @if($req->admin_response)
                                                    <span class="line-clamp-2 italic">“{{ $req->admin_response }}”</span>
                                                @else
                                                    <span class="text-gray-400 italic">Under atelier review</span>
                                                @endif
                                            </td>
                                            <td class="py-3 pl-3 text-right">
                                                <a href="{{ $req->getWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer"
                                                   title="Contact Support on WhatsApp"
                                                   class="inline-flex items-center gap-1 text-[11px] text-[#25D366] hover:text-[#1EBE5D] font-bold uppercase tracking-wider">
                                                    WhatsApp &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Column: Privé Card Preview & Policy Information --}}
            <div class="space-y-6">
                {{-- Digital Card View --}}
                @include('prive-club.card', ['membership' => $membership])

                {{-- Policy Disclosure Card --}}
                <div class="bg-white border border-[#D4AF37]/30 p-5 rounded-xl shadow-xs text-xs space-y-3">
                    <h4 class="font-bold uppercase tracking-wider text-[11px] text-[#0E261C]">
                        Atelier Membership Policy
                    </h4>
                    <p class="text-gray-600 leading-relaxed text-[11px]">
                        <strong>Cancellations:</strong> Membership cancellations are individually reviewed by our concierge. If approved, future auto-renewals are halted while your privilege access remains active until the end of your prepaid validity period.
                    </p>
                    <p class="text-gray-600 leading-relaxed text-[11px]">
                        <strong>Refunds:</strong> Refund requests are governed by our fine jewellery subscription terms. Membership refunds require manual verification and approval. No automated debit or credit occurs without concierge authorization.
                    </p>
                    <p class="text-gray-600 leading-relaxed text-[11px]">
                        <strong>Upgrades:</strong> When upgrading your tier, you pay only the incremental price difference. You are never billed twice for identical coverage.
                    </p>
                    <div class="pt-2 border-t border-gray-100">
                        <span class="block text-[10px] text-gray-400 uppercase tracking-wider">Official Atelier Concierge</span>
                        <span class="font-bold text-[#0E261C] text-xs">+91 87912 27210</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL 1: REQUEST CANCELLATION & OPTIONAL REFUND --}}
    <div x-show="cancelModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="cancelModal = false" 
             class="bg-white border-2 border-[#9B1B30] rounded-xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative">
            <button type="button" @click="cancelModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-2.5 mb-2">
                <span class="text-[10px] uppercase font-bold tracking-[0.2em] text-[#9B1B30]">Atelier Subscription Action</span>
            </div>
            <h3 class="font-display text-2xl font-bold text-[#0E261C]">
                Request Membership Cancellation
            </h3>
            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                Please provide your reason for cancelling <strong>{{ $membership->tier_name }}</strong>. All cancellation and refund requests require administrative review.
            </p>

            <form action="{{ route('account.membership.cancel') }}" method="POST" 
                  @submit="if(submitting) { $event.preventDefault(); return false; } submitting = true;" 
                  class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs uppercase font-bold text-gray-700 tracking-wider mb-1">
                        Reason for Cancellation *
                    </label>
                    <select name="reason" x-model="cancelReason" required
                            class="w-full text-xs p-2.5 border border-gray-300 rounded focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37]">
                        <option value="">Select a reason...</option>
                        <option value="Not using membership benefits frequently">Not using membership benefits frequently</option>
                        <option value="Relocating or temporary pause">Relocating or temporary pause</option>
                        <option value="Financial considerations">Financial considerations</option>
                        <option value="Dissatisfied with privilege perks">Dissatisfied with privilege perks</option>
                        <option value="Switching to a different patronage tier">Switching to a different patronage tier</option>
                        <option value="Other bespoke reasons">Other bespoke reasons</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs uppercase font-bold text-gray-700 tracking-wider mb-1">
                        Optional Comments / Feedback
                    </label>
                    <textarea name="customer_notes" x-model="cancelNotes" rows="3"
                              placeholder="Share your experience to help us refine VRAZIO Atelier services..."
                              class="w-full text-xs p-2.5 border border-gray-300 rounded focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37]"></textarea>
                </div>

                {{-- Refund Request Checkbox & Disclosure --}}
                <div class="p-3.5 bg-[#FAF8F5] border border-[#D4AF37]/50 rounded-xs space-y-2">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="request_refund" value="1" x-model="requestRefund"
                               class="mt-0.5 rounded text-[#0E261C] focus:ring-[#D4AF37]">
                        <span class="text-xs font-bold text-[#0E261C]">
                            Request a refund review alongside cancellation
                        </span>
                    </label>
                    <p class="text-[11px] text-gray-600 leading-relaxed pl-6">
                        “Your refund request will be reviewed by our administration team. Refund eligibility and approval are subject to our membership refund policy. Our support team will contact you through WhatsApp if additional information is required.”
                    </p>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="cancelModal = false"
                            class="px-4 py-2.5 text-xs font-semibold text-gray-600 hover:text-gray-900 uppercase tracking-wider cursor-pointer">
                        Keep Membership
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-[#9B1B30] text-white hover:bg-red-800 text-xs font-bold uppercase tracking-wider rounded transition-colors shadow-xs cursor-pointer flex items-center gap-2">
                        <span x-show="!submitting">Confirm & Submit Request</span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Submitting...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: CONFIRM MEMBERSHIP UPGRADE --}}
    <div x-show="upgradeModal" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div @click.away="upgradeModal = false" 
             class="bg-white border-2 border-[#D4AF37] rounded-xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative">
            <button type="button" @click="upgradeModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-2 mb-2">
                <span class="text-[10px] uppercase font-bold tracking-[0.2em] text-[#D4AF37]">Tier Elevation Protocol</span>
            </div>
            <h3 class="font-display text-2xl font-bold text-[#0E261C]">
                Upgrade to <span x-text="selectedUpgrade ? selectedUpgrade.name : ''"></span>
            </h3>
            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                Confirm your request to upgrade from <strong>{{ $membership->tier_name }}</strong>. Upgrade requires concierge confirmation. You will not be charged twice.
            </p>

            <div class="my-4 p-4 bg-[#FAF8F5] border border-gray-200 rounded-lg text-xs space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-500">Current Plan Fee:</span>
                    <span class="font-mono font-semibold">₹{{ number_format((float) $membership->fee_paid, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Target Plan Fee:</span>
                    <span class="font-mono font-semibold" x-text="'₹' + (selectedUpgrade ? parseFloat(selectedUpgrade.price).toFixed(2) : '0.00')"></span>
                </div>
                <div class="pt-2 border-t border-gray-200 flex justify-between font-bold text-sm text-[#0E261C]">
                    <span>Additional Investment Required:</span>
                    <span class="font-mono text-emerald-700" x-text="selectedUpgrade ? selectedUpgrade.formatted_difference : ''"></span>
                </div>
            </div>

            <form action="{{ route('account.membership.upgrade') }}" method="POST" 
                  @submit="if(submitting) { $event.preventDefault(); return false; } submitting = true;" 
                  class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="target_plan_id" :value="selectedUpgradeId">

                <div>
                    <label class="block text-xs uppercase font-bold text-gray-700 tracking-wider mb-1">
                        Upgrade Request Notes (Optional)
                    </label>
                    <textarea name="customer_notes" rows="2"
                              placeholder="Any bespoke instructions or preferred payment method (UPI / Card / Concierge Transfer)..."
                              class="w-full text-xs p-2.5 border border-gray-300 rounded focus:border-[#D4AF37] focus:ring-1 focus:ring-[#D4AF37]"></textarea>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="upgradeModal = false"
                            class="px-4 py-2.5 text-xs font-semibold text-gray-600 hover:text-gray-900 uppercase tracking-wider cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 bg-[#0E261C] text-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-bold uppercase tracking-wider rounded transition-colors shadow-xs cursor-pointer flex items-center gap-2">
                        <span x-show="!submitting">Confirm Upgrade Request</span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="animate-spin w-3.5 h-3.5 text-[#D4AF37]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Submitting...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
