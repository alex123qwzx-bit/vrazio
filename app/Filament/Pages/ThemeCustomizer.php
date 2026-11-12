<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\SiteThemeSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ThemeCustomizer extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Storefront Design';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Theme & Layout Customizer';

    protected static string $view = 'filament.pages.theme-customizer';

    public ?array $data = [];

    public function mount(): void
    {
        $activeSections = SiteThemeSetting::getSetting('active_sections', [
            'hero', 'categories', 'heritage', 'showcase', 'testimonials', 'faq'
        ]);

        $heroConfig = SiteThemeSetting::getSetting('hero_config', [
            'headline' => 'Exquisite Craftsmanship, Timeless Elegance',
            'subtitle' => 'Discover our curated collection of BIS Hallmarked fine jewellery, crafted by master artisans.',
            'background_image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=2000&q=80',
            'cta_label' => 'Explore Collections',
            'cta_link' => '/collections',
        ]);

        $colorOverrides = SiteThemeSetting::getSetting('color_overrides', [
            'primary' => '#0E261C',
            'accent_gold' => '#D4AF37',
            'button_color' => '#9B1B30',
            'button_radius' => '0px',
        ]);

        $announcementBar = SiteThemeSetting::getSetting('announcement_bar', [
            'enabled' => true,
            'text' => 'Complimentary Armoured Doorstep Transit & BIS Hallmarked Certification across India',
            'link' => '/shipping-policy',
            'speed' => 30,
            'bg_color' => '#0E261C',
        ]);

        $layoutStyle = SiteThemeSetting::getSetting('layout_style', 'classic_atelier');

        $this->form->fill([
            'active_sections' => is_array($activeSections) ? $activeSections : json_decode($activeSections, true),
            'hero_headline' => $heroConfig['headline'] ?? '',
            'hero_subtitle' => $heroConfig['subtitle'] ?? '',
            'hero_background_image' => $heroConfig['background_image'] ?? '',
            'hero_cta_label' => $heroConfig['cta_label'] ?? 'Explore Collections',
            'hero_cta_link' => $heroConfig['cta_link'] ?? '/collections',
            'color_primary' => $colorOverrides['primary'] ?? '#0E261C',
            'color_accent_gold' => $colorOverrides['accent_gold'] ?? '#D4AF37',
            'color_button' => $colorOverrides['button_color'] ?? '#9B1B30',
            'button_radius' => $colorOverrides['button_radius'] ?? '0px',
            'layout_style' => is_string($layoutStyle) ? $layoutStyle : 'classic_atelier',
            'announcement_enabled' => (bool) ($announcementBar['enabled'] ?? true),
            'announcement_text' => $announcementBar['text'] ?? '',
            'announcement_link' => $announcementBar['link'] ?? '',
            'announcement_bg_color' => $announcementBar['bg_color'] ?? '#0E261C',
            'announcement_speed' => $announcementBar['speed'] ?? 30,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('CustomizerTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Section Manager')
                            ->icon('heroicon-o-view-columns')
                            ->schema([
                                Forms\Components\Section::make('Storefront Homepage Flow')
                                    ->description('Activate and arrange the sequential presence of sections on the VRAZIO master storefront.')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('active_sections')
                                            ->label('Active Sections')
                                            ->options([
                                                'hero' => '1. Haute Hero Showcase (Immersive Visuals & Brand Hook)',
                                                'categories' => '2. Curated Categories Grid (Rings, Necklaces, Solitaires)',
                                                'heritage' => '3. Master Atelier Craftsmanship & Heritage Story',
                                                'showcase' => '4. Interactive 4K Gemstone Showcase Carousel',
                                                'testimonials' => '5. Patron Testimonials & Reviews',
                                                'faq' => '6. Frequently Asked Questions Accordion',
                                            ])
                                            ->columns(1)
                                            ->required(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Hero Banner')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Forms\Components\Section::make('Hero Master Configuration')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('hero_headline')
                                            ->label('Headline')
                                            ->required()
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('hero_subtitle')
                                            ->label('Subtitle / Tagline')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('hero_background_image')
                                            ->label('Background Image URL')
                                            ->url()
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('hero_cta_label')
                                            ->label('CTA Button Label')
                                            ->default('Explore Collections'),
                                        Forms\Components\TextInput::make('hero_cta_link')
                                            ->label('CTA Button Link')
                                            ->default('/collections'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Design Palette')
                            ->icon('heroicon-o-swatch')
                            ->schema([
                                Forms\Components\Section::make('Luxury Color Grading & Styling')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\ColorPicker::make('color_primary')
                                            ->label('Primary Atelier Green')
                                            ->default('#0E261C'),
                                        Forms\Components\ColorPicker::make('color_accent_gold')
                                            ->label('Accent Luxury Gold')
                                            ->default('#D4AF37'),
                                        Forms\Components\ColorPicker::make('color_button')
                                            ->label('Action CTA Ruby')
                                            ->default('#9B1B30'),
                                        Forms\Components\Select::make('button_radius')
                                            ->label('Button Corner Geometry')
                                            ->options([
                                                '0px' => 'Sharp Luxury (0px)',
                                                '4px' => 'Subtle Atelier Bevel (4px)',
                                                '8px' => 'Modern Curvature (8px)',
                                            ])
                                            ->default('0px'),
                                        Forms\Components\Select::make('layout_style')
                                            ->label('Architectural Layout Archetype')
                                            ->options([
                                                'classic_atelier' => 'Classic Atelier — Grand Serif & Imperial Green',
                                                'modern_minimal' => 'Modern Minimalist — Clean Lines & High Contrast',
                                                'royal_heritage' => 'Royal Heritage — Deep Emerald & Gilded Borders',
                                            ])
                                            ->default('classic_atelier')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Announcement Bar')
                            ->icon('heroicon-o-megaphone')
                            ->schema([
                                Forms\Components\Section::make('Global Announcement Ticker')
                                    ->columns(2)
                                    ->schema([
                                        Forms\Components\Toggle::make('announcement_enabled')
                                            ->label('Enable Announcement Ticker')
                                            ->default(true)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('announcement_text')
                                            ->label('Broadcast Message')
                                            ->columnSpanFull(),
                                        Forms\Components\ColorPicker::make('announcement_bg_color')
                                            ->label('Bar Background Color')
                                            ->default('#0E261C'),
                                        Forms\Components\TextInput::make('announcement_link')
                                            ->label('Hyperlink Destination')
                                            ->default('/shipping-policy'),
                                        Forms\Components\TextInput::make('announcement_speed')
                                            ->label('Ticker Speed (seconds)')
                                            ->numeric()
                                            ->default(30),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $userId = Auth::id();

        SiteThemeSetting::setSetting('active_sections', $state['active_sections'], $userId);

        SiteThemeSetting::setSetting('hero_config', [
            'headline' => $state['hero_headline'],
            'subtitle' => $state['hero_subtitle'],
            'background_image' => $state['hero_background_image'],
            'cta_label' => $state['hero_cta_label'],
            'cta_link' => $state['hero_cta_link'],
        ], $userId);

        SiteThemeSetting::setSetting('color_overrides', [
            'primary' => $state['color_primary'],
            'accent_gold' => $state['color_accent_gold'],
            'button_color' => $state['color_button'],
            'button_radius' => $state['button_radius'],
        ], $userId);

        SiteThemeSetting::setSetting('announcement_bar', [
            'enabled' => (bool) $state['announcement_enabled'],
            'text' => $state['announcement_text'],
            'bg_color' => $state['announcement_bg_color'],
            'link' => $state['announcement_link'],
            'speed' => (int) $state['announcement_speed'],
        ], $userId);

        SiteThemeSetting::setSetting('layout_style', $state['layout_style'], $userId);

        Cache::forget('site_theme_settings');

        Notification::make()
            ->title('Storefront Atelier Layout Synchronized')
            ->body('All theme parameters have been committed and edge caches purged.')
            ->success()
            ->send();
    }
}
