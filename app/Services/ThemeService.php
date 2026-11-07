<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteThemeSetting;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    /**
     * Cache key for all theme settings.
     */
    private const string CACHE_KEY = 'site_theme_settings';

    /**
     * Cache TTL in seconds (24 hours).
     */
    private const int CACHE_TTL = 86400;

    /**
     * Get all theme settings as a key-value array.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): array {
            try {
                return SiteThemeSetting::all()
                    ->pluck('value', 'key')
                    ->toArray();
            } catch (\Exception) {
                return $this->defaults();
            }
        });
    }

    /**
     * Get a single theme setting by key.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getSettings();

        return $settings[$key] ?? $default;
    }

    /**
     * Update (or create) a theme setting and bust the cache.
     */
    public function updateSetting(string $key, mixed $value, ?int $userId = null): void
    {
        SiteThemeSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'updated_by' => $userId,
            ]
        );

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get the list of active homepage sections.
     *
     * @return list<string>
     */
    public function getActiveSections(): array
    {
        $raw = $this->getSetting('active_sections');

        if (is_string($raw)) {
            return json_decode($raw, true) ?? [];
        }

        return is_array($raw)
            ? $raw
            : ['hero', 'categories', 'heritage', 'showcase', 'testimonials', 'faq'];
    }

    /**
     * Get hero section configuration.
     *
     * @return array{headline: string, subtitle: string, background_image: string, cta_label: string, cta_link: string}
     */
    public function getHeroConfig(): array
    {
        $raw = $this->getSetting('hero_config');

        if (is_string($raw)) {
            return json_decode($raw, true) ?? $this->defaultHeroConfig();
        }

        return is_array($raw) ? $raw : $this->defaultHeroConfig();
    }

    /**
     * Get color override values.
     *
     * @return array{primary: string, accent_gold: string, button_color: string}
     */
    public function getColorOverrides(): array
    {
        $raw = $this->getSetting('color_overrides');

        if (is_string($raw)) {
            return json_decode($raw, true) ?? $this->defaultColors();
        }

        return is_array($raw) ? $raw : $this->defaultColors();
    }

    /**
     * Get announcement bar configuration.
     *
     * @return array{enabled: bool, text: string, link: string, speed: int}
     */
    public function getAnnouncementBar(): array
    {
        $raw = $this->getSetting('announcement_bar');

        if (is_string($raw)) {
            return json_decode($raw, true) ?? $this->defaultAnnouncementBar();
        }

        return is_array($raw) ? $raw : $this->defaultAnnouncementBar();
    }

    /**
     * Get the current layout style identifier.
     */
    public function getLayoutStyle(): string
    {
        $value = $this->getSetting('layout_style', 'classic_atelier');

        return is_string($value) ? $value : 'classic_atelier';
    }

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    /**
     * Full set of default theme settings for when the DB is empty.
     */
    private function defaults(): array
    {
        return [
            'active_sections' => json_encode(['hero', 'categories', 'heritage', 'showcase', 'testimonials', 'faq']),
            'hero_config' => json_encode($this->defaultHeroConfig()),
            'color_overrides' => json_encode($this->defaultColors()),
            'announcement_bar' => json_encode($this->defaultAnnouncementBar()),
            'layout_style' => 'classic_atelier',
        ];
    }

    private function defaultHeroConfig(): array
    {
        return [
            'headline' => 'Exquisite Craftsmanship, Timeless Elegance',
            'subtitle' => 'Discover our curated collection of BIS Hallmarked fine jewellery, crafted by master artisans.',
            'background_image' => '/images/hero-bg.jpg',
            'cta_label' => 'Explore Collections',
            'cta_link' => '/collections',
        ];
    }

    private function defaultColors(): array
    {
        return [
            'primary' => '#0E261C',
            'accent_gold' => '#D4AF37',
            'button_color' => '#9B1B30',
        ];
    }

    private function defaultAnnouncementBar(): array
    {
        return [
            'enabled' => true,
            'text' => 'Complimentary Armoured Doorstep Transit & BIS Hallmarked Certification across India',
            'link' => '/shipping',
            'speed' => 30,
        ];
    }
}
