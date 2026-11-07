# VRAZIO &mdash; Haute Joaillerie E-Commerce Platform

Production-ready, ultra-luxury fine jewellery e-commerce platform built with **Modern PHP (PHP 8.2 / 8.3 / 8.4)**, **Laravel 11**, **Livewire 3**, **Tailwind CSS**, and **Filament v3** for the Admin Atelier.

---

## 💎 Brand Identity & Aesthetics

- **Primary Brand Color**: Deep Forest Green (`#0E261C` / `rgb(14, 38, 28)`)
- **Accent Luxury Gold**: Metallic Gold (`#D4AF37` / `#C5A028`) & Light Gold (`#F3E5AB`)
- **Action CTA Accent**: Crimson Ruby (`#9B1B30`) for checkout & order buttons
- **Backgrounds**: Ivory Cream (`#FAF8F5`) & Deep Dark Green (`#0A1C15`)
- **Typography**: Georgia / Playfair Display (Serif) for headings; Inter / System (Sans) for UI body.

---

## 🏛️ Architecture & Features

### 1. Storefront Experience (Blade + Livewire + Tailwind)
- **Dynamic Homepage Flow**: Controlled from admin atelier (Hero, Curated Categories, Master Craftsmanship Heritage, 4K Gemstone Carousel Showcase, Patron Testimonials with AggregateRating schema, FAQ Accordion with FAQPage schema).
- **High-Jewellery Catalog**: Live filterable search (Category, Precious Alloy, Price Range, GIA/IGI Certification, and Sorting).
- **4K Gemstone Product Page**: Interactive zoom lens, diamond appraisal dossier, Government BIS Hallmarking verification card, gross/net gold weights, and specifications.
- **Bespoke Bag**: Client-side storage and session synchronization with 3% Jewellery GST calculation and complimentary armoured transit.
- **3-Step Checkout with Mandatory Two-Factor Shield**:
  1. Patron Dossier & Address.
  2. Armoured Transit Logistics & Fast2SMS OTP verification.
  3. Razorpay Escrow payment initialization with server-side signature validation.
- **Order Confirmation & Tracking**: Printable transit insurance docket, order timeline (Inscribed &rarr; Hallmarking &rarr; Armoured Transit &rarr; Delivered), and courier escrow status.

### 2. Admin Atelier (Filament v3)
- **Catalogue**: High jewellery product CRUD with multiple image uploads, JSON specs editor, metal purity selection, BIS hallmark badges, and stock control.
- **Orders & Patrons**: View order dockets, client contact, address JSON, financial summary, and **Advance Fulfillment Workflow** (Inscribed &rarr; Hallmarking &rarr; Armoured Transit &rarr; Delivered).
- **Clientele Management**: Patron directory with registration timestamps and order tallies.
- **Storefront Theme & Layout Customizer**:
  - Reorder and toggle homepage sections.
  - Customise Hero headlines, background imagery, and CTA routes.
  - Luxury Color Palette grading & Button corner radius geometry (0px Sharp Luxury vs 4px Subtle Bevel vs 8px Modern).
  - Broadcast Announcement Bar ticker manager.
  - Automatic cache invalidation (`Cache::forget('site_theme_settings')`).

### 3. Security & Modern PHP Safeguards
- **PHP 8.2 / 8.3 / 8.4 ready**: Strict types, typed properties, match expressions, and readonly properties.
- **Timing Attack Mitigation**: `hash_equals()` validation for OTP tokens and HMAC-SHA256 Razorpay signatures.
- **Cryptographic OTP Generation**: `random_int(100000, 999999)` with SHA-256 one-way hashing; automatic 5-minute expiration and 30-second cooldown rate-limiting.
- **Two-Factor Checkout Gate**: Server-side middleware and controller validation enforcing `session('order_phone_verified') === $phone` before order creation.

---

## 🚀 Getting Started Locally

### Prerequisites
- PHP 8.2+ (Recommended: PHP 8.3 or PHP 8.4)
- Composer 2.x
- Node.js 18+ & NPM
- MySQL 8.0+

### Installation Steps

1. **Clone & Environment Setup**:
   ```bash
   cp .env.example .env
   ```
   Configure your database credentials and API keys in `.env`.

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

4. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```
   *Seeds 24 detailed high-jewellery products, default layout settings, and default admin user:*
   - **Admin Email**: `admin@vrazio.com`
   - **Admin Password**: `password`

5. **Install Node Packages & Build Assets**:
   ```bash
   npm install
   npm run build # Or npm run dev for hot-reloading
   ```

6. **Serve the Application**:
   ```bash
   php artisan serve
   ```
   - Storefront: `http://localhost:8000`
   - Admin Atelier: `http://localhost:8000/admin`

---

## ⚡ Hostinger Production Deployment Guide

Refer to [DEPLOYMENT_HOSTINGER.md](./DEPLOYMENT_HOSTINGER.md) for full configuration steps covering PHP 8.2/8.3 OPcache optimization, Cloudflare edge caching rules, and MySQL index verification.
