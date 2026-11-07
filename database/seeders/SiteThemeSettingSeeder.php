<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SiteThemeSetting;
use Illuminate\Database\Seeder;

class SiteThemeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'active_sections',
                'value' => [
                    'hero',
                    'categories',
                    'heritage',
                    'showcase',
                    'testimonials',
                    'faq',
                ],
            ],
            [
                'key' => 'hero_config',
                'value' => [
                    'headline' => 'Exquisite Craftsmanship, Timeless Elegance',
                    'subtitle' => 'Discover our curated collection of BIS Hallmarked fine jewellery, crafted by master artisans.',
                    'background_image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=2000&q=80',
                    'cta_label' => 'Explore Collections',
                    'cta_link' => '/collections',
                ],
            ],
            [
                'key' => 'color_overrides',
                'value' => [
                    'primary' => '#0E261C',
                    'accent_gold' => '#D4AF37',
                    'button_color' => '#9B1B30',
                    'button_radius' => '0px',
                ],
            ],
            [
                'key' => 'announcement_bar',
                'value' => [
                    'enabled' => true,
                    'text' => 'Complimentary Armoured Doorstep Transit & BIS Hallmarked Certification across India',
                    'link' => '/shipping-policy',
                    'speed' => 30,
                    'bg_color' => '#0E261C',
                ],
            ],
            [
                'key' => 'layout_style',
                'value' => 'classic_atelier',
            ],
        ];

        foreach ($settings as $setting) {
            SiteThemeSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
