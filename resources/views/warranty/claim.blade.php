@extends('layouts.app')

@section('title', 'Claim Atelier Warranty | Order #' . $order->order_number)
@section('meta_description', 'Register a master craftsmanship warranty claim for your VRAZIO creation.')

@section('content')
<div class="bg-[#FAF8F5] min-h-screen py-12 md:py-16" 
     x-data="{
         files: [],
         isSubmitting: false,
         errorMessage: '',
         handleFileSelect(event) {
             this.errorMessage = '';
             const selectedFiles = Array.from(event.target.files);

             if (this.files.length + selectedFiles.length > 3) {
                 this.errorMessage = 'You can upload a maximum of 3 photos in total.';
                 return;
             }

             selectedFiles.forEach(file => {
                 if (file.size > 5 * 1024 * 1024) {
                     this.errorMessage = `File ${file.name} exceeds the 5MB size limit.`;
                     return;
                 }

                 if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                     this.errorMessage = `File ${file.name} is not a valid format (JPG, PNG, or WebP).`;
                     return;
                 }

                 const reader = new FileReader();
                 reader.onload = (e) => {
                     this.files.push({
                         file: file,
                         name: file.name,
                         size: (file.size / 1024 / 1024).toFixed(2) + ' MB',
                         preview: e.target.result
                     });
                     this.syncInput();
                 };
                 reader.readAsDataURL(file);
             });
         },
         removeFile(index) {
             this.files.splice(index, 1);
             this.syncInput();
         },
         syncInput() {
             const dt = new DataTransfer();
             this.files.forEach(item => dt.items.add(item.file));
             this.$refs.actualFileInput.files = dt.files;
         },
         validateAndSubmit(e) {
             this.errorMessage = '';
             if (this.files.length < 2) {
                 this.errorMessage = 'Please upload at least 2 clear photos showing the craftsmanship defect.';
                 e.preventDefault();
                 return false;
             }
             if (this.files.length > 3) {
                 this.errorMessage = 'Please select a maximum of 3 photos.';
                 e.preventDefault();
                 return false;
             }
             this.isSubmitting = true;
         }
     }">

    <div class="max-w-4xl mx-auto px-6">
        {{-- Breadcrumbs --}}
        <x-breadcrumbs :links="[
            'My Orders' => route('orders.index'),
            'Order #' . $order->order_number => route('order.track', $order->order_number),
            'Claim Warranty' => ''
        ]" />

        {{-- Page Header --}}
        <div class="text-center my-8">
            <div class="inline-flex items-center gap-3 border-y border-[#D4AF37]/40 py-1 px-5 mb-4">
                <span class="w-1.5 h-1.5 rotate-45 bg-[#D4AF37]"></span>
                <span class="text-[11px] uppercase tracking-[0.3em] text-[#D4AF37] font-semibold">
                    Master Atelier Guarantee
                </span>
                <span class="w-1.5 h-1.5 rotate-45 bg-[#D4AF37]"></span>
            </div>

            <h1 class="font-display text-3xl sm:text-5xl font-bold text-[#0E261C]">
                Warranty Claim
            </h1>
            <p class="text-xs sm:text-sm text-[#0E261C]/75 font-light mt-3 max-w-xl mx-auto">
                Every VRAZIO piece is covered under our quality warranty. Please fill out the form below with 2 to 3 photos of the issue so our team can review and assist you.
            </p>
        </div>

        {{-- WhatsApp Quick Help Card --}}
        <div class="mb-8 bg-emerald-50 border border-emerald-300 p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 rounded-lg shadow-sm">
            <div class="flex items-center gap-3.5 text-center sm:text-left">
                <div class="w-12 h-12 rounded-full bg-[#25D366] text-white flex items-center justify-center shrink-0 shadow">
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.99.54 1.761.821 2.796.821h.005c3.18 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.766-5.773-5.768zm7.135 5.769c-.002 3.935-3.201 7.134-7.136 7.134-1.257 0-2.434-.332-3.468-.944l-4.562 1.196 1.217-4.444c-.672-1.076-1.027-2.327-1.026-3.61.002-3.935 3.202-7.135 7.137-7.135 3.935 0 7.138 3.2 7.138 7.133z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-950">Need Quick Help on WhatsApp?</h3>
                    <p class="text-xs text-emerald-800">You can message us directly on WhatsApp at <strong>+91 89796 22310</strong> for immediate assistance with your warranty claim.</p>
                </div>
            </div>
            <a href="https://wa.me/918979622310?text=Hi%20VRAZIO%2C%20I%20need%20help%20with%20my%20warranty%20claim%20for%20order%20{{ $order->order_number }}" 
               target="_blank" 
               rel="noopener noreferrer"
               class="px-5 py-2.5 bg-[#25D366] hover:bg-[#1ebd5b] text-white text-xs font-bold uppercase tracking-wider rounded shadow transition-all whitespace-nowrap flex items-center gap-2">
                <span>Chat on WhatsApp</span>
                <span>→</span>
            </a>
        </div>

        {{-- Alerts --}}
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-[#9B1B30] text-red-800 text-xs sm:text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-[#9B1B30] text-red-800 text-xs sm:text-sm">
                <p class="font-bold mb-1">Please fix the following errors:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Claim Form Card --}}
        <div class="bg-white border border-[#D4AF37]/30 shadow-xl overflow-hidden">
            {{-- Order Summary Header Bar --}}
            <div class="bg-[#0E261C] text-[#FAF8F5] p-6 border-b border-[#D4AF37]/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] uppercase font-mono tracking-widest text-[#D4AF37]">Order Number</span>
                    <h2 class="font-display text-lg sm:text-xl font-bold text-[#FAF8F5] tracking-wide">
                        {{ $order->order_number }}
                    </h2>
                </div>
                <div class="flex items-center gap-4 text-xs font-light text-[#FAF8F5]/80">
                    <div>
                        <span class="text-[10px] uppercase text-gray-400 block">Delivered On</span>
                        <span>{{ $order->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="border-l border-white/20 pl-4">
                        <span class="text-[10px] uppercase text-gray-400 block">Status</span>
                        <span class="text-green-400 font-semibold uppercase tracking-wider text-[11px]">Delivered ✓</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('warranty.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  @submit="validateAndSubmit($event)"
                  class="p-6 sm:p-10 space-y-8">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                {{-- Section 1: Read-Only Order Particulars --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-[#0E261C] border-b border-gray-100 pb-2">
                        1. Order Details
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">
                                Order Reference
                            </label>
                            <input type="text" value="{{ $order->order_number }}" readonly 
                                   class="w-full text-xs font-mono bg-gray-50 border border-gray-200 px-3.5 py-2.5 text-gray-700 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-500 font-semibold mb-1">
                                Product / Jewellery Title
                            </label>
                            <input type="text" name="product_name" value="{{ old('product_name', $defaultProductName) }}" 
                                   class="w-full text-xs bg-gray-50 border border-gray-200 px-3.5 py-2.5 text-gray-700 focus:bg-white focus:border-[#D4AF37] focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Section 2: Contact Information --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-[#0E261C] border-b border-gray-100 pb-2">
                        2. Your Contact Information
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-[#0E261C] font-semibold mb-1">
                                Full Name <span class="text-[#9B1B30]">*</span>
                            </label>
                            <input type="text" name="customer_name" required
                                   value="{{ old('customer_name', auth()->user()?->name ?? $order->client_name) }}"
                                   placeholder="Your Full Name"
                                   class="w-full text-xs border border-gray-200 px-3.5 py-2.5 focus:border-[#D4AF37] focus:outline-none bg-white">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-[#0E261C] font-semibold mb-1">
                                Phone Number <span class="text-[#9B1B30]">*</span>
                            </label>
                            <input type="tel" name="phone" required
                                   value="{{ old('phone', auth()->user()?->phone ?? $order->client_phone) }}"
                                   placeholder="e.g. +91 89796 22310"
                                   class="w-full text-xs border border-gray-200 px-3.5 py-2.5 focus:border-[#D4AF37] focus:outline-none bg-white">
                        </div>

                        <div>
                            <label class="block text-xs uppercase tracking-wider text-[#0E261C] font-semibold mb-1">
                                Email Address <span class="text-[#9B1B30]">*</span>
                            </label>
                            <input type="email" name="email" required
                                   value="{{ old('email', auth()->user()?->email ?? $order->client_email) }}"
                                   placeholder="e.g. yourname@gmail.com"
                                   class="w-full text-xs border border-gray-200 px-3.5 py-2.5 focus:border-[#D4AF37] focus:outline-none bg-white">
                        </div>
                    </div>
                </div>

                {{-- Section 3: Issue Description --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-[#0E261C] border-b border-gray-100 pb-2">
                        3. Issue Details
                    </h3>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-[#0E261C] font-semibold mb-1">
                            What is the issue? <span class="text-[#9B1B30]">*</span>
                        </label>
                        <select name="issue_type" required 
                                class="w-full text-xs border border-gray-200 px-3.5 py-2.5 focus:border-[#D4AF37] focus:outline-none bg-white">
                            <option value="">Select the type of issue...</option>
                            @foreach($issueTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('issue_type') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider text-[#0E261C] font-semibold mb-1">
                            Please describe the issue in detail <span class="text-[#9B1B30]">*</span>
                        </label>
                        <textarea name="description" rows="5" required minlength="15" maxlength="3000"
                                  placeholder="Please describe what happened, when you noticed the defect, and what needs repair..."
                                  class="w-full text-xs border border-gray-200 p-3.5 focus:border-[#D4AF37] focus:outline-none bg-white leading-relaxed">{{ old('description') }}</textarea>
                        <span class="text-[10px] text-gray-400 block mt-1">Minimum 15 characters. The more details you share, the faster we can help.</span>
                    </div>
                </div>

                {{-- Section 4: Photographic Evidence Upload --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-[#0E261C] border-b border-gray-100 pb-2 flex items-center justify-between">
                        <span>4. Photos of the Item (Upload 2 to 3 Photos)</span>
                        <span class="text-[10px] text-gray-400 font-normal">Max 5MB each (JPG, PNG, WebP)</span>
                    </h3>

                    {{-- Hidden Actual File Input Synchronized via Alpine --}}
                    <input type="file" name="photos[]" multiple x-ref="actualFileInput" class="hidden">

                    {{-- Visible Drag & Drop / Click Zone --}}
                    <div class="border-2 border-dashed border-[#D4AF37]/40 hover:border-[#D4AF37] p-8 text-center bg-[#FAF8F5] cursor-pointer transition-colors relative"
                         @click="$refs.pickerInput.click()">
                        <input type="file" accept="image/jpeg,image/png,image/webp" multiple x-ref="pickerInput" @change="handleFileSelect($event)" class="hidden">
                        
                        <div class="space-y-2">
                            <div class="w-12 h-12 bg-white text-[#D4AF37] border border-[#D4AF37]/30 mx-auto flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div class="text-xs text-[#0E261C] font-semibold">
                                Click or drag defect photographs here
                            </div>
                            <p class="text-[11px] text-gray-500">
                                Please capture at least 1 close-up macro view and 1 full-piece angle. (2 to 3 photos required)
                            </p>
                        </div>
                    </div>

                    {{-- Client-side Error Message --}}
                    <div x-show="errorMessage" x-cloak class="p-3 bg-red-50 text-red-700 text-xs border border-red-200">
                        <span x-text="errorMessage"></span>
                    </div>

                    {{-- Image Previews Container with Remove Buttons --}}
                    <div class="grid grid-cols-3 gap-4 mt-4" x-show="files.length > 0" x-cloak>
                        <template x-for="(file, index) in files" :key="index">
                            <div class="relative bg-white border border-[#D4AF37]/30 overflow-hidden shadow-sm aspect-square group">
                                <img :src="file.preview" :alt="file.name" class="w-full h-full object-cover">
                                
                                {{-- Remove Overlay Button --}}
                                <button type="button" 
                                        @click.stop="removeFile(index)" 
                                        class="absolute top-2 right-2 w-7 h-7 bg-[#9B1B30] text-white rounded-full flex items-center justify-center hover:bg-red-700 shadow-md transition-colors"
                                        title="Remove Photo">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>

                                <div class="absolute inset-x-0 bottom-0 bg-black/60 backdrop-blur-sm p-1.5 text-[9px] text-white truncate px-2 text-center">
                                    <span x-text="file.name"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Submit Actions with Double-Submit Prevention --}}
                <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="{{ route('orders.index') }}" 
                       class="text-xs uppercase tracking-widest text-gray-500 hover:text-[#0E261C] transition-colors order-2 sm:order-1">
                        &larr; Return to My Orders
                    </a>

                    <button type="submit" 
                            :disabled="isSubmitting"
                            class="w-full sm:w-auto px-10 py-4 bg-[#0E261C] text-[#D4AF37] border border-[#D4AF37] hover:bg-[#D4AF37] hover:text-[#0E261C] text-xs font-semibold uppercase tracking-[0.25em] transition-all duration-300 shadow-xl flex items-center justify-center gap-3 order-1 sm:order-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        <template x-if="!isSubmitting">
                            <span class="flex items-center gap-2">
                                <span>Submit Warranty Claim</span>
                                <span>&rarr;</span>
                            </span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-[#D4AF37]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Submitting to Atelier Vault...</span>
                            </span>
                        </template>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
