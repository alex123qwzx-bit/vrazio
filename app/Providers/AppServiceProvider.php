<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Share theme and homepage settings with all views
        view()->composer('*', function ($view) {
            $themeSettings = Cache::remember('site_theme_settings', 86400, function () {
                if (class_exists(\App\Models\SiteThemeSetting::class)) {
                    try {
                        return \App\Models\SiteThemeSetting::all()->pluck('value', 'key')->toArray();
                    } catch (\Exception $e) {
                        return $this->defaultThemeSettings();
                    }
                }
                return $this->defaultThemeSettings();
            });

            $homepageConfig = app(\App\Services\HomepageManagerService::class)->getPublished();

            $view->with('themeSettings', $themeSettings)
                 ->with('homepageConfig', $homepageConfig);
        });
    }

    /**
     * Default theme settings when database is not yet seeded.
     */
    private function defaultThemeSettings(): array
    {
        return [
            'active_sections' => json_encode([
                'hero', 'categories', 'heritage', 'showcase', 'testimonials', 'faq'
            ]),
            'hero_config' => json_encode([
                'headline' => 'Exquisite Craftsmanship, Timeless Elegance',
                'subtitle' => 'Discover our curated collection of BIS Hallmarked fine jewellery, crafted by master artisans.',
                'background_image' => '/images/hero-bg.jpg',
                'cta_label' => 'Explore Collections',
                'cta_link' => '/collections',
            ]),
            'color_overrides' => json_encode([
                'primary' => '#0E261C',
                'accent_gold' => '#D4AF37',
                'button_color' => '#9B1B30',
            ]),
            'announcement_bar' => json_encode([
                'enabled' => true,
                'text' => 'Complimentary Armoured Doorstep Transit & BIS Hallmarked Certification across India',
                'link' => '/shipping',
                'speed' => 30,
            ]),
            'layout_style' => 'classic_atelier',
        ];
    }
}
