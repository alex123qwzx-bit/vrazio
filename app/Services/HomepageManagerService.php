<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\SiteThemeSetting;
use Illuminate\Support\Facades\Cache;

class HomepageManagerService
{
    public const string CACHE_PUBLISHED_KEY = 'site_homepage_published';
    public const string SETTING_PUBLISHED = 'homepage_published';
    public const string SETTING_DRAFT = 'homepage_draft';
    public const string SETTING_PREVIOUS = 'homepage_previous_published';
    public const int CACHE_TTL = 86400;

    /**
     * Get the live published homepage configuration.
     *
     * @return array<string, mixed>
     */
    public function getPublished(): array
    {
        return Cache::remember(self::CACHE_PUBLISHED_KEY, self::CACHE_TTL, function (): array {
            $raw = SiteThemeSetting::getSetting(self::SETTING_PUBLISHED);
            if (!empty($raw) && is_array($raw)) {
                return $this->mergeWithDefaults($raw);
            }

            return $this->getDefaults();
        });
    }

    /**
     * Get the draft homepage configuration (for editing / admin preview).
     *
     * @return array<string, mixed>
     */
    public function getDraft(): array
    {
        $raw = SiteThemeSetting::getSetting(self::SETTING_DRAFT);
        if (!empty($raw) && is_array($raw)) {
            return $this->mergeWithDefaults($raw);
        }

        return $this->getPublished();
    }

    /**
     * Save working changes to draft.
     */
    public function saveDraft(array $data, ?int $userId = null): void
    {
        SiteThemeSetting::setSetting(self::SETTING_DRAFT, $data, $userId);
    }

    /**
     * Publish changes to live storefront.
     */
    public function publish(array $data, ?int $userId = null): void
    {
        // Snapshot current published configuration for rollback capability
        $currentPublished = SiteThemeSetting::getSetting(self::SETTING_PUBLISHED) ?: $this->getPublished();
        SiteThemeSetting::setSetting(self::SETTING_PREVIOUS, $currentPublished, $userId);

        // Commit to live published and sync draft
        SiteThemeSetting::setSetting(self::SETTING_PUBLISHED, $data, $userId);
        SiteThemeSetting::setSetting(self::SETTING_DRAFT, $data, $userId);

        // Also sync active_sections with legacy ThemeCustomizer if present
        if (!empty($data['sections_order'])) {
            $activeList = [];
            foreach ($data['sections_order'] as $item) {
                if (!empty($item['is_active']) && !empty($item['id'])) {
                    $activeList[] = $item['id'];
                }
            }
            SiteThemeSetting::setSetting('active_sections', $activeList, $userId);
        }

        $this->clearCache();
    }

    /**
     * Restore previous published version.
     */
    public function restorePrevious(?int $userId = null): bool
    {
        $previous = SiteThemeSetting::getSetting(self::SETTING_PREVIOUS);
        if (empty($previous) || !is_array($previous)) {
            return false;
        }

        $current = SiteThemeSetting::getSetting(self::SETTING_PUBLISHED);
        if (!empty($current)) {
            SiteThemeSetting::setSetting(self::SETTING_PREVIOUS, $current, $userId);
        }

        SiteThemeSetting::setSetting(self::SETTING_PUBLISHED, $previous, $userId);
        SiteThemeSetting::setSetting(self::SETTING_DRAFT, $previous, $userId);

        $this->clearCache();

        return true;
    }

    /**
     * Check if a previous snapshot is available for restoration.
     */
    public function hasPrevious(): bool
    {
        $previous = SiteThemeSetting::getSetting(self::SETTING_PREVIOUS);
        return !empty($previous) && is_array($previous);
    }

    /**
     * Clear caches.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_PUBLISHED_KEY);
        Cache::forget('site_theme_settings');
    }

    /**
     * Merge saved data with defaults to ensure all required keys exist.
     */
    protected function mergeWithDefaults(array $data): array
    {
        $defaults = $this->getDefaults();

        foreach ($defaults as $key => $defaultValue) {
            if (!isset($data[$key])) {
                $data[$key] = $defaultValue;
            } elseif (is_array($defaultValue) && is_array($data[$key])) {
                // If it's an associative config block, ensure missing subkeys are preserved
                if (array_keys($defaultValue) !== range(0, count($defaultValue) - 1)) {
                    $data[$key] = array_merge($defaultValue, $data[$key]);
                }
            }
        }

        return $data;
    }

    /**
     * Complete defaults based on VRAZIO master storefront.
     */
    public function getDefaults(): array
    {
        return [
            // Section controls & sequencing
            'sections_order' => [
                ['id' => 'hero', 'name' => '1. Haute Hero Showcase Slider', 'is_active' => true],
                ['id' => 'trust_strip', 'name' => '2. Hallmark & Certification Trust Strip', 'is_active' => true],
                ['id' => 'categories', 'name' => '3. Curated Atelier Tiers (Categories)', 'is_active' => true],
                ['id' => 'featured_products', 'name' => '4. Signature Pieces (Featured Products)', 'is_active' => true],
                ['id' => 'heritage', 'name' => '5. Master Atelier Craftsmanship & Heritage', 'is_active' => true],
                ['id' => 'gallery', 'name' => '6. Master Atelier Gallery (4K Gemstone Showcase)', 'is_active' => true],
                ['id' => 'testimonials', 'name' => '7. Patron Testimonials & Reviews', 'is_active' => true],
                ['id' => 'faq', 'name' => '8. Clientele Concierge & FAQs', 'is_active' => true],
            ],

            // Hero Slider
            'hero_slides' => [
                [
                    'badge' => 'Haute Joaillerie • Atelier Privé',
                    'headline' => 'Exquisite Craftsmanship, Timeless Elegance',
                    'subtitle' => 'Discover our curated collection of BIS Hallmarked fine jewellery & solitaires, crafted by master artisans.',
                    'description' => '',
                    'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=2000&q=85',
                    'image_mobile' => '',
                    'cta_primary_label' => 'Explore Collections',
                    'cta_primary_link' => '/collections',
                    'cta_secondary_label' => 'Atelier Heritage',
                    'cta_secondary_link' => '/about',
                    'is_active' => true,
                    'start_at' => null,
                    'end_at' => null,
                    'sort_order' => 1,
                ],
                [
                    'badge' => 'Haute Couture & Royal Apparel',
                    'headline' => 'Imperial Zardozi & Banarasi Heirlooms',
                    'subtitle' => 'Bespoke bridal lehengas, handwoven Banarasi Katan silks, and royal micro-velvet sherwanis embellished with authentic 24KT gold zari.',
                    'description' => '',
                    'image' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=2000&q=85',
                    'image_mobile' => '',
                    'cta_primary_label' => 'Explore Couture',
                    'cta_primary_link' => '/couture',
                    'cta_secondary_label' => 'Bridal Lehengas',
                    'cta_secondary_link' => '/couture?tag=Bridal+Lehenga',
                    'is_active' => true,
                    'start_at' => null,
                    'end_at' => null,
                    'sort_order' => 2,
                ],
                [
                    'badge' => 'Rare Solitaires & Sovereign Emeralds',
                    'headline' => 'Flawless Brilliance, Forged in Platinum',
                    'subtitle' => 'Triple-excellent cut D-Flawless diamonds certified by GIA and untreated Zambian emeralds, sculpted in dense Platinum 950 and 18KT gold.',
                    'description' => '',
                    'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=2000&q=85',
                    'image_mobile' => '',
                    'cta_primary_label' => 'View Solitaires',
                    'cta_primary_link' => '/collections/solitaires',
                    'cta_secondary_label' => 'Private Concierge',
                    'cta_secondary_link' => '/contact',
                    'is_active' => true,
                    'start_at' => null,
                    'end_at' => null,
                    'sort_order' => 3,
                ],
            ],

            // Categories Section
            'category_section' => [
                'is_active' => true,
                'badge' => 'Haute Collections',
                'heading' => 'Curated Atelier Tiers',
                'description' => 'Explore signature compositions forged in 18KT yellow gold, rose gold, and platinum.',
                'display_limit' => 6,
                'items' => [
                    [
                        'name' => 'Rings',
                        'slug' => 'rings',
                        'category_id' => null,
                        'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=800&q=80',
                        'description' => 'Solitaires, cocktail & eternity bands crafted in 18KT gold.',
                        'is_active' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'name' => 'Necklaces',
                        'slug' => 'necklaces',
                        'category_id' => null,
                        'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=800&q=80',
                        'description' => 'Imperial chokers, diamond pendants & bespoke haute colliers.',
                        'is_active' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'name' => 'Bracelets',
                        'slug' => 'bracelets',
                        'category_id' => null,
                        'image' => 'https://images.unsplash.com/photo-1611591475810-7e3fbe3f8f1c?auto=format&fit=crop&w=800&q=80',
                        'description' => 'Tennis bracelets and sculpted cuffs in Platinum 950.',
                        'is_active' => true,
                        'sort_order' => 3,
                    ],
                    [
                        'name' => 'Earrings',
                        'slug' => 'earrings',
                        'category_id' => null,
                        'image' => 'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=800&q=80',
                        'description' => 'Cascade chandeliers, diamond studs & royal drop earrings.',
                        'is_active' => true,
                        'sort_order' => 4,
                    ],
                    [
                        'name' => 'Solitaires',
                        'slug' => 'solitaires',
                        'category_id' => null,
                        'image' => 'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?auto=format&fit=crop&w=800&q=80',
                        'description' => 'GIA certified triple-excellent cut diamond solitaires.',
                        'is_active' => true,
                        'sort_order' => 5,
                    ],
                    [
                        'name' => 'Haute Couture',
                        'slug' => 'couture',
                        'category_id' => null,
                        'image' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=800&q=80',
                        'description' => 'Bridal lehengas, pure Banarasi silks & royal velvet sherwanis.',
                        'is_active' => true,
                        'sort_order' => 6,
                    ],
                ],
            ],

            // Featured Products Section
            'featured_products_section' => [
                'is_active' => true,
                'badge' => 'Atelier Curations',
                'heading' => 'Signature Pieces',
                'description' => 'Individually hallmarked creations exemplifying our highest standards of craftsmanship.',
                'collection_type' => 'featured', // 'featured', 'trending', 'best_selling', 'new_arrivals', 'manual'
                'selected_product_ids' => [],
                'limit' => 8,
                'button_label' => 'View Entire High Jewellery Vault',
                'button_link' => '/collections',
            ],

            // Brand Heritage Section
            'heritage_section' => [
                'is_active' => true,
                'badge' => 'Heritage & Provenance',
                'heading' => 'Where Alchemy Meets Imperial Precision',
                'paragraph_1' => 'At VRAZIO, jewellery transcends ornamentation — it becomes an enduring legacy. Conceived in the heart of artisanal heritage, our atelier merges age-old hand-setting practices with micron-level metallurgical precision.',
                'paragraph_2' => 'Each solitaire is individually curated by certified gemologists, adhering to triple-excellent proportions. Every ounce of gold is 100% BIS Hallmarked, ensuring absolute purity and ethical provenance.',
                'main_image' => 'https://images.unsplash.com/photo-1531995811006-35cb42e1a022?auto=format&fit=crop&w=1000&q=80',
                'floating_badge_title' => '3 Generations',
                'floating_badge_subtitle' => 'Of Master Goldsmithing',
                'stats' => [
                    ['value' => '0.01%', 'label' => 'Top Tier Gemstone Selection'],
                    ['value' => '100%', 'label' => 'Insured Armoured Transit'],
                ],
                'button_label' => 'Discover Our Private Atelier Process',
                'button_link' => '/about',
            ],

            // Master Atelier Gallery (4K Gemstone Showcase)
            'gallery_section' => [
                'is_active' => true,
                'badge' => 'Master Atelier Gallery',
                'heading' => '4K Gemstone Showcase',
                'items' => [
                    [
                        'title' => 'The Empress Emerald Solitaire Collar',
                        'category' => 'Haute Necklaces',
                        'purity' => '18KT Royal Yellow Gold & Zambian Emerald',
                        'price' => '₹14,50,000',
                        'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=1200&q=85',
                        'link' => '/collections',
                        'is_active' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'Celestial Flawless Diamond Solitaire Ring',
                        'category' => 'Bespoke Rings',
                        'purity' => 'Platinum 950 & 3.20ct D-Flawless',
                        'price' => '₹28,90,000',
                        'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=1200&q=85',
                        'link' => '/collections',
                        'is_active' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'title' => 'Imperial Cascade Chandbali Earrings',
                        'category' => 'Heritage Earrings',
                        'purity' => '22KT Hallmarked Gold & Uncut Polki',
                        'price' => '₹9,80,000',
                        'image' => 'https://images.unsplash.com/photo-1630019852942-f89202989a59?auto=format&fit=crop&w=1200&q=85',
                        'link' => '/collections',
                        'is_active' => true,
                        'sort_order' => 3,
                    ],
                ],
            ],

            // Trust Badges & Promises
            'trust_badges_section' => [
                'is_active' => true,
                'items' => [
                    [
                        'title' => '100% BIS Hallmarked Gold & Silk Mark',
                        'icon' => 'check-badge',
                        'description' => 'Government certified purity assurance on every gram',
                        'link' => '/shipping-policy',
                        'is_active' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'title' => 'GIA • IGI • SGL Certified Diamonds',
                        'icon' => 'sparkles',
                        'description' => 'International gemological laboratory certification dossiers',
                        'link' => '/about',
                        'is_active' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'title' => 'Complimentary Armoured Doorstep Transit',
                        'icon' => 'shield-check',
                        'description' => 'Fully insured specialized transit with secure OTP handover',
                        'link' => '/shipping-policy',
                        'is_active' => true,
                        'sort_order' => 3,
                    ],
                ],
            ],

            // Frequently Asked Questions
            'faq_section' => [
                'is_active' => true,
                'badge' => 'Clientele Concierge',
                'heading' => 'Frequently Asked Questions',
                'description' => 'Insights into hallmarking authentication, armoured transit logistics, and our master atelier protocols.',
                'items' => [
                    [
                        'q' => 'How does VRAZIO verify BIS Hallmarking and Gemstone Certifications?',
                        'a' => 'Every gold jewel crafted at VRAZIO bears the official Bureau of Indian Standards (BIS) hallmark, featuring the BIS logo, purity grade in karats/fineness (e.g., 750 for 18KT, 916 for 22KT), 6-digit HUID code, and assaying center emblem. All solitaire diamonds above 0.30 carats come with GIA, IGI, or SGL dossiers verifiable online in global gemological registries.',
                        'is_active' => true,
                        'sort_order' => 1,
                    ],
                    [
                        'q' => 'What is the procedure for Armoured Doorstep Transit and Insurance?',
                        'a' => 'All orders are dispatched in tamper-evident sealed security vaults via specialized armoured logistical partners (such as Sequel Logistics / Brink’s). Shipments are 100% insured until delivery. For security reasons, transit requires OTP confirmation by the patron upon handover.',
                        'is_active' => true,
                        'sort_order' => 2,
                    ],
                    [
                        'q' => 'Why is Phone SMS OTP verification mandatory before payment?',
                        'a' => 'To safeguard patron security and ensure secure consignment delivery, every order requires two-factor validation via SMS OTP powered by Fast2SMS. This ensures only authorized patrons commission high-value pieces and provides seamless dispatch coordination.',
                        'is_active' => true,
                        'sort_order' => 3,
                    ],
                    [
                        'q' => 'What payment gateways are supported for high jewellery purchases?',
                        'a' => 'We utilize Razorpay with enterprise-level 256-bit encryption. We accept Indian and International Credit/Debit Cards, Net Banking across all major banks, UPI, and verified Bank Wire / NEFT / RTGS transfers with escrow settlement.',
                        'is_active' => true,
                        'sort_order' => 4,
                    ],
                    [
                        'q' => 'Can I commission a bespoke design or customized solitaire setting?',
                        'a' => 'Yes. Our private atelier welcomes bespoke commissions. You may schedule a private consultation with our Master Goldsmith to select custom rough gems, custom metal alloys, and personalized hand inscriptions.',
                        'is_active' => true,
                        'sort_order' => 5,
                    ],
                    [
                        'q' => 'What is the VRAZIO return and exchange policy?',
                        'a' => 'We offer a 15-day complimentary inspection and exchange period for standard collection pieces in original condition with security tags intact. Bespoke and custom engraved commissions are non-refundable but covered under our Lifetime Craftsmanship Warranty.',
                        'is_active' => true,
                        'sort_order' => 6,
                    ],
                ],
            ],

            // Testimonials & Reviews Section
            'testimonials_section' => [
                'is_active' => true,
                'badge' => 'What People Say',
                'heading' => 'Customer Reviews',
                'description' => 'Read real experiences from patrons who commissioned fine jewellery and couture from VRAZIO.',
                'review_count' => 3,
                'source' => 'featured_reviews', // 'featured_reviews' or 'curated'
                'fallback_testimonials' => [
                    [
                        'name' => 'Maharani Gayatri D.',
                        'subtitle' => 'Commissioned Solitaire Ring • Mumbai',
                        'rating' => 5,
                        'quote' => 'The craftsmanship on my solitaire engagement ring is breathtaking. The GIA documentation and armoured transit escort delivered directly to our Mumbai residence gave us total peace of mind.',
                        'is_verified' => true,
                    ],
                    [
                        'name' => 'Vikramaditya S.',
                        'subtitle' => 'Fine Jewellery Collector • New Delhi',
                        'rating' => 5,
                        'quote' => 'VRAZIO\'s verification protocol via SMS OTP and secure Razorpay escrow was seamless. The imperial emerald collar we ordered surpassed our expectations in every light.',
                        'is_verified' => true,
                    ],
                    [
                        'name' => 'Dr. Ananya Roy',
                        'subtitle' => 'Patron • Bengaluru',
                        'rating' => 5,
                        'quote' => 'The tennis bracelet in Platinum 950 is pure poetry on the wrist. Hallmarked with BIS stamp and accompanied by comprehensive appraisal certificate.',
                        'is_verified' => true,
                    ],
                ],
            ],

            // Footer Configuration
            'footer' => [
                'brand_description' => 'Fine Jewellery and Couture crafted with care and passion. Every piece brings together beauty, certified quality, and timeless style.',
                'phone' => '+91 87912 27210',
                'email' => 'vrazioofficail@gmail.com',
                'business_hours' => 'Monday — Saturday: 10:00 AM — 8:00 PM IST',
                'instagram_url' => 'https://www.instagram.com/vrazio.official/',
                'facebook_url' => 'https://www.facebook.com/share/1CJBJZbnkG/',
                'whatsapp_number' => '+91 87912 27210',
                'whatsapp_text' => 'Hi VRAZIO, I have a query',
                'copyright_text' => '© ' . date('Y') . ' VRAZIO Haute Joaillerie. All rights reserved.',
                'udyam_registration' => 'UDYAM-UP-59-0089382',
                'show_payment_badges' => true,
                'show_bis_badge' => true,
            ],

            // SEO Configuration
            'seo' => [
                'meta_title' => 'Haute Joaillerie & Master Atelier | VRAZIO Atelier',
                'meta_description' => 'Welcome to VRAZIO Haute Joaillerie. Discover handcrafted fine jewellery, BIS hallmarked gold, GIA certified diamond solitaires, and royal bridal couture.',
                'meta_keywords' => 'VRAZIO, fine jewellery, diamond ring, haute joaillerie, BIS hallmarked gold, GIA certified diamond, luxury jewellery India, bespoke couture',
                'canonical_url' => '',
                'og_title' => 'Haute Joaillerie & Fine Jewellery | VRAZIO',
                'og_description' => 'VRAZIO — Haute Joaillerie. Discover exquisite handcrafted fine jewellery, BIS hallmarked and GIA certified.',
                'og_image' => '/images/og-default.png',
                'enable_faq_schema' => true,
                'enable_organization_schema' => true,
            ],
        ];
    }
}
