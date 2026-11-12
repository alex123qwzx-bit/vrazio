<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Services\HomepageManagerService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class HomepageManager extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationGroup = 'Storefront Design';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Homepage Manager';

    protected static ?string $navigationLabel = 'Homepage Manager';

    protected static string $view = 'filament.pages.homepage-manager';

    public ?array $data = [];

    protected HomepageManagerService $service;

    public function boot(HomepageManagerService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $draft = $this->service->getDraft();
        $this->form->fill($draft);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview Draft')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn (): string => route('home', ['preview' => 'draft']))
                ->openUrlInNewTab(),

            Action::make('save_draft')
                ->label('Save Draft')
                ->icon('heroicon-o-document-check')
                ->color('info')
                ->action(fn () => $this->saveDraft()),

            Action::make('restore_previous')
                ->label('Restore Previous Version')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Restore Previously Published Version')
                ->modalDescription('Are you sure you want to revert the storefront to the last published snapshot? Current live changes will be overwritten.')
                ->modalSubmitActionLabel('Yes, Revert to Previous')
                ->visible(fn (): bool => $this->service->hasPrevious())
                ->action(fn () => $this->restorePrevious()),

            Action::make('publish')
                ->label('Publish Changes')
                ->icon('heroicon-o-check-badge')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Publish to Live VRAZIO Storefront')
                ->modalDescription('This action will immediately deploy all draft sections, banners, collections, and styling to public visitors.')
                ->modalSubmitActionLabel('Publish Now')
                ->action(fn () => $this->publish()),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('HomepageTabs')
                    ->tabs([
                        // Tab 1: Section Sequencing & Visibility
                        Forms\Components\Tabs\Tab::make('Section Controls')
                            ->icon('heroicon-o-queue-list')
                            ->schema([
                                Forms\Components\Section::make('Storefront Section Arrangement')
                                    ->description('Enable, disable, and drag-and-drop to reorder sections on the VRAZIO master landing page.')
                                    ->schema([
                                        Forms\Components\Repeater::make('sections_order')
                                            ->label('Homepage Sections (Top to Bottom)')
                                            ->schema([
                                                Forms\Components\Hidden::make('id'),
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Section Name')
                                                            ->disabled()
                                                            ->columnSpan(2),
                                                        Forms\Components\Toggle::make('is_active')
                                                            ->label('Active on Storefront')
                                                            ->default(true)
                                                            ->columnSpan(1),
                                                    ]),
                                            ])
                                            ->addable(false)
                                            ->deletable(false)
                                            ->reorderable()
                                            ->orderColumn('sort')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 2: Hero Slider
                        Forms\Components\Tabs\Tab::make('Hero Slider')
                            ->icon('heroicon-o-sparkles')
                            ->schema([
                                Forms\Components\Section::make('Haute Hero Slider Management')
                                    ->description('Manage dynamic multi-slide carousel banners with badges, headlines, links, and scheduled visibility.')
                                    ->schema([
                                        Forms\Components\Repeater::make('hero_slides')
                                            ->label('Hero Slides')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('badge')
                                                            ->label('Atelier Badge')
                                                            ->placeholder('e.g. Haute Joaillerie • Atelier Privé')
                                                            ->columnSpan(2),
                                                        Forms\Components\Toggle::make('is_active')
                                                            ->label('Slide Active')
                                                            ->default(true)
                                                            ->columnSpan(1),
                                                    ]),
                                                Forms\Components\TextInput::make('headline')
                                                    ->label('Main Headline')
                                                    ->required()
                                                    ->placeholder('Exquisite Craftsmanship, Timeless Elegance')
                                                    ->columnSpanFull(),
                                                Forms\Components\Textarea::make('subtitle')
                                                    ->label('Subtitle / Tagline')
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\FileUpload::make('image')
                                                            ->label('Desktop Banner Image (1920x1080 Recommended)')
                                                            ->disk('public')
                                                            ->directory('homepage/hero')
                                                            ->image()
                                                            ->imageEditor(),
                                                        Forms\Components\FileUpload::make('image_mobile')
                                                            ->label('Mobile Banner Image (Optional 800x1200)')
                                                            ->disk('public')
                                                            ->directory('homepage/hero')
                                                            ->image(),
                                                    ]),
                                                Forms\Components\Grid::make(4)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('cta_primary_label')
                                                            ->label('Primary CTA Text')
                                                            ->default('Explore Collections'),
                                                        Forms\Components\TextInput::make('cta_primary_link')
                                                            ->label('Primary CTA URL')
                                                            ->default('/collections'),
                                                        Forms\Components\TextInput::make('cta_secondary_label')
                                                            ->label('Secondary CTA Text')
                                                            ->default('Atelier Heritage'),
                                                        Forms\Components\TextInput::make('cta_secondary_link')
                                                            ->label('Secondary CTA URL')
                                                            ->default('/about'),
                                                    ]),
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\DateTimePicker::make('start_at')
                                                            ->label('Schedule Start Date (Optional)'),
                                                        Forms\Components\DateTimePicker::make('end_at')
                                                            ->label('Schedule End Date (Optional)'),
                                                    ]),
                                            ])
                                            ->addActionLabel('+ Add New Hero Slide')
                                            ->reorderable()
                                            ->orderColumn('sort_order')
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['headline'] ?? 'Hero Slide')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 3: Curated Categories
                        Forms\Components\Tabs\Tab::make('Categories')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema([
                                Forms\Components\Section::make('Curated Atelier Tiers Section')
                                    ->description('Select existing categories from your database and curate which ones appear on the homepage.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('category_section.badge')
                                                    ->label('Section Badge')
                                                    ->default('Haute Collections'),
                                                Forms\Components\TextInput::make('category_section.heading')
                                                    ->label('Section Heading')
                                                    ->default('Curated Atelier Tiers'),
                                                Forms\Components\Toggle::make('category_section.is_active')
                                                    ->label('Section Active')
                                                    ->default(true),
                                            ]),
                                        Forms\Components\Textarea::make('category_section.description')
                                            ->label('Section Description')
                                            ->rows(2)
                                            ->default('Explore signature compositions forged in 18KT yellow gold, rose gold, and platinum.')
                                            ->columnSpanFull(),
                                        Forms\Components\Repeater::make('category_section.items')
                                            ->label('Curated Homepage Category Cards')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\Select::make('category_id')
                                                            ->label('Link to Database Category (Optional)')
                                                            ->options(Category::pluck('name', 'id'))
                                                            ->searchable()
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                                if ($state && ($cat = Category::find($state))) {
                                                                    $set('name', $cat->name);
                                                                    $set('slug', $cat->slug);
                                                                    if ($cat->image) {
                                                                        $set('image', $cat->image);
                                                                    }
                                                                }
                                                            }),
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Display Title')
                                                            ->required(),
                                                        Forms\Components\TextInput::make('slug')
                                                            ->label('Category Slug / URL')
                                                            ->required(),
                                                    ]),
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\FileUpload::make('image')
                                                            ->label('Card Visual')
                                                            ->disk('public')
                                                            ->directory('homepage/categories')
                                                            ->image()
                                                            ->columnSpan(2),
                                                        Forms\Components\Toggle::make('is_active')
                                                            ->label('Card Active')
                                                            ->default(true)
                                                            ->columnSpan(1),
                                                    ]),
                                                Forms\Components\TextInput::make('description')
                                                    ->label('Short Description (Revealed on Hover)')
                                                    ->columnSpanFull(),
                                            ])
                                            ->addActionLabel('+ Add Category Card')
                                            ->reorderable()
                                            ->orderColumn('sort_order')
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Category Card')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 4: Featured Products
                        Forms\Components\Tabs\Tab::make('Featured Products')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Forms\Components\Section::make('Signature Pieces Showcase')
                                    ->description('Curate products highlighted directly from your live inventory database.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('featured_products_section.badge')
                                                    ->label('Section Badge')
                                                    ->default('Atelier Curations'),
                                                Forms\Components\TextInput::make('featured_products_section.heading')
                                                    ->label('Section Heading')
                                                    ->default('Signature Pieces'),
                                                Forms\Components\Toggle::make('featured_products_section.is_active')
                                                    ->label('Section Active')
                                                    ->default(true),
                                            ]),
                                        Forms\Components\Textarea::make('featured_products_section.description')
                                            ->label('Section Description')
                                            ->rows(2)
                                            ->default('Individually hallmarked creations exemplifying our highest standards of craftsmanship.')
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Select::make('featured_products_section.collection_type')
                                                    ->label('Collection Source Type')
                                                    ->options([
                                                        'featured' => 'Database "Featured" Flag',
                                                        'new_arrivals' => 'Newest Arrivals (Latest in stock)',
                                                        'manual' => 'Handpicked Specific Products',
                                                    ])
                                                    ->default('featured')
                                                    ->live(),
                                                Forms\Components\TextInput::make('featured_products_section.limit')
                                                    ->label('Maximum Products Shown')
                                                    ->numeric()
                                                    ->default(8),
                                                Forms\Components\TextInput::make('featured_products_section.button_label')
                                                    ->label('Vault Button Label')
                                                    ->default('View Entire High Jewellery Vault'),
                                            ]),
                                        Forms\Components\Select::make('featured_products_section.selected_product_ids')
                                            ->label('Handpicked Products (Used when source is Handpicked)')
                                            ->multiple()
                                            ->options(Product::pluck('title', 'id'))
                                            ->searchable()
                                            ->visible(fn (Forms\Get $get): bool => $get('featured_products_section.collection_type') === 'manual')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 5: Heritage & Story
                        Forms\Components\Tabs\Tab::make('Heritage')
                            ->icon('heroicon-o-book-open')
                            ->schema([
                                Forms\Components\Section::make('Master Atelier Craftsmanship & Story')
                                    ->description('Customize the brand narrative, artisan photography, and statistics.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('heritage_section.badge')
                                                    ->label('Section Badge')
                                                    ->default('Heritage & Provenance'),
                                                Forms\Components\TextInput::make('heritage_section.heading')
                                                    ->label('Main Heading')
                                                    ->default('Where Alchemy Meets Imperial Precision'),
                                                Forms\Components\Toggle::make('heritage_section.is_active')
                                                    ->label('Section Active')
                                                    ->default(true),
                                            ]),
                                        Forms\Components\Textarea::make('heritage_section.paragraph_1')
                                            ->label('Story Paragraph 1')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('heritage_section.paragraph_2')
                                            ->label('Story Paragraph 2')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\FileUpload::make('heritage_section.main_image')
                                                    ->label('Atelier Craftsmanship Visual')
                                                    ->disk('public')
                                                    ->directory('homepage/heritage')
                                                    ->image(),
                                                Forms\Components\TextInput::make('heritage_section.floating_badge_title')
                                                    ->label('Floating Badge Value')
                                                    ->placeholder('3 Generations'),
                                                Forms\Components\TextInput::make('heritage_section.floating_badge_subtitle')
                                                    ->label('Floating Badge Label')
                                                    ->placeholder('Of Master Goldsmithing'),
                                            ]),
                                        Forms\Components\Repeater::make('heritage_section.stats')
                                            ->label('Achievement Statistics')
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('value')
                                                            ->label('Metric / Value')
                                                            ->placeholder('e.g. 0.01%'),
                                                        Forms\Components\TextInput::make('label')
                                                            ->label('Description')
                                                            ->placeholder('e.g. Top Tier Gemstone Selection'),
                                                    ]),
                                            ])
                                            ->defaultItems(2)
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('heritage_section.button_label')
                                                    ->label('CTA Button Text')
                                                    ->default('Discover Our Private Atelier Process'),
                                                Forms\Components\TextInput::make('heritage_section.button_link')
                                                    ->label('CTA Button Link')
                                                    ->default('/about'),
                                            ]),
                                    ]),
                            ]),

                        // Tab 6: 4K Gemstone Showcase Gallery
                        Forms\Components\Tabs\Tab::make('Atelier Gallery')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\Section::make('Master Atelier 4K Gemstone Showcase')
                                    ->description('Present bespoke showcase gallery items with purity, pricing, and links.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('gallery_section.badge')
                                                    ->label('Section Badge')
                                                    ->default('Master Atelier Gallery'),
                                                Forms\Components\TextInput::make('gallery_section.heading')
                                                    ->label('Section Heading')
                                                    ->default('4K Gemstone Showcase'),
                                                Forms\Components\Toggle::make('gallery_section.is_active')
                                                    ->label('Section Active')
                                                    ->default(true),
                                            ]),
                                        Forms\Components\Repeater::make('gallery_section.items')
                                            ->label('Showcase Gallery Items')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('title')
                                                            ->label('Piece Title')
                                                            ->required(),
                                                        Forms\Components\TextInput::make('category')
                                                            ->label('Category Tag')
                                                            ->placeholder('e.g. Haute Necklaces'),
                                                        Forms\Components\TextInput::make('price')
                                                            ->label('Price Display')
                                                            ->placeholder('e.g. ₹14,50,000'),
                                                    ]),
                                                Forms\Components\TextInput::make('purity')
                                                    ->label('Metal & Purity Specifications')
                                                    ->placeholder('e.g. 18KT Royal Yellow Gold & Zambian Emerald')
                                                    ->columnSpanFull(),
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\FileUpload::make('image')
                                                            ->label('High-Resolution Visual')
                                                            ->disk('public')
                                                            ->directory('homepage/gallery')
                                                            ->image()
                                                            ->columnSpan(2),
                                                        Forms\Components\Toggle::make('is_active')
                                                            ->label('Visible in Showcase')
                                                            ->default(true)
                                                            ->columnSpan(1),
                                                    ]),
                                                Forms\Components\TextInput::make('link')
                                                    ->label('Destination Link')
                                                    ->default('/collections'),
                                            ])
                                            ->addActionLabel('+ Add Showcase Item')
                                            ->reorderable()
                                            ->orderColumn('sort_order')
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Showcase Item')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 7: Trust Badges & Promises
                        Forms\Components\Tabs\Tab::make('Trust Badges')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Forms\Components\Section::make('Trust Indicators & Substantive Brand Promises')
                                    ->description('Configure Bureau of Indian Standards (BIS) hallmarking, GIA certifications, and armoured transit.')
                                    ->schema([
                                        Forms\Components\Toggle::make('trust_badges_section.is_active')
                                            ->label('Display Trust Strip on Homepage')
                                            ->default(true),
                                        Forms\Components\Repeater::make('trust_badges_section.items')
                                            ->label('Trust Promises')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('title')
                                                            ->label('Promise Headline')
                                                            ->required()
                                                            ->columnSpan(2),
                                                        Forms\Components\Toggle::make('is_active')
                                                            ->label('Active')
                                                            ->default(true)
                                                            ->columnSpan(1),
                                                    ]),
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('description')
                                                            ->label('Supporting Detail'),
                                                        Forms\Components\TextInput::make('link')
                                                            ->label('Verification Link')
                                                            ->default('/shipping-policy'),
                                                    ]),
                                            ])
                                            ->addActionLabel('+ Add Trust Indicator')
                                            ->reorderable()
                                            ->orderColumn('sort_order')
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Trust Badge')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 8: Clientele FAQs
                        Forms\Components\Tabs\Tab::make('FAQs')
                            ->icon('heroicon-o-question-mark-circle')
                            ->schema([
                                Forms\Components\Section::make('Frequently Asked Questions')
                                    ->description('Manage client concierge answers. Structured FAQPage schema is automatically generated.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('faq_section.badge')
                                                    ->label('Section Badge')
                                                    ->default('Clientele Concierge'),
                                                Forms\Components\TextInput::make('faq_section.heading')
                                                    ->label('Section Heading')
                                                    ->default('Frequently Asked Questions'),
                                                Forms\Components\Toggle::make('faq_section.is_active')
                                                    ->label('Section Active')
                                                    ->default(true),
                                            ]),
                                        Forms\Components\Textarea::make('faq_section.description')
                                            ->label('Section Description')
                                            ->rows(2)
                                            ->default('Insights into hallmarking authentication, armoured transit logistics, and our master atelier protocols.')
                                            ->columnSpanFull(),
                                        Forms\Components\Repeater::make('faq_section.items')
                                            ->label('FAQ Items')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('q')
                                                            ->label('Question')
                                                            ->required()
                                                            ->columnSpan(2),
                                                        Forms\Components\Toggle::make('is_active')
                                                            ->label('Active')
                                                            ->default(true)
                                                            ->columnSpan(1),
                                                    ]),
                                                Forms\Components\Textarea::make('a')
                                                    ->label('Answer')
                                                    ->required()
                                                    ->rows(3)
                                                    ->columnSpanFull(),
                                            ])
                                            ->addActionLabel('+ Add FAQ')
                                            ->reorderable()
                                            ->orderColumn('sort_order')
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['q'] ?? 'FAQ')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 9: Customer Reviews
                        Forms\Components\Tabs\Tab::make('Customer Reviews')
                            ->icon('heroicon-o-star')
                            ->schema([
                                Forms\Components\Section::make('Homepage Patron Testimonials')
                                    ->description('Controls the review presence on the landing page. Genuine customer reviews marked as "Featured" in the Customer Reviews section will display automatically.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('testimonials_section.badge')
                                                    ->label('Section Badge')
                                                    ->default('What People Say'),
                                                Forms\Components\TextInput::make('testimonials_section.heading')
                                                    ->label('Section Heading')
                                                    ->default('Customer Reviews'),
                                                Forms\Components\Toggle::make('testimonials_section.is_active')
                                                    ->label('Section Active')
                                                    ->default(true),
                                            ]),
                                        Forms\Components\Textarea::make('testimonials_section.description')
                                            ->label('Section Description')
                                            ->rows(2)
                                            ->default('Read real experiences from patrons who ordered fine jewellery and clothes from VRAZIO.')
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('testimonials_section.source')
                                                    ->label('Review Feed Source')
                                                    ->options([
                                                        'featured_reviews' => 'Approved Customer Reviews (Marked Featured in Reviews)',
                                                        'curated' => 'Curated Patron Quotes (Manual Fallbacks)',
                                                    ])
                                                    ->default('featured_reviews'),
                                                Forms\Components\TextInput::make('testimonials_section.review_count')
                                                    ->label('Max Reviews to Display')
                                                    ->numeric()
                                                    ->default(3),
                                            ]),
                                        Forms\Components\Repeater::make('testimonials_section.fallback_testimonials')
                                            ->label('Curated Luxury Fallbacks (Used if no reviews are featured yet)')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Patron Name')
                                                            ->required(),
                                                        Forms\Components\TextInput::make('subtitle')
                                                            ->label('Commission / Location')
                                                            ->placeholder('e.g. Commissioned Solitaire Ring • Mumbai'),
                                                        Forms\Components\Select::make('rating')
                                                            ->label('Star Rating')
                                                            ->options([5 => '5 Stars', 4 => '4 Stars', 3 => '3 Stars'])
                                                            ->default(5),
                                                    ]),
                                                Forms\Components\Textarea::make('quote')
                                                    ->label('Review Quote')
                                                    ->required()
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ])
                                            ->addActionLabel('+ Add Curated Testimonial')
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 10: Footer Management
                        Forms\Components\Tabs\Tab::make('Footer')
                            ->icon('heroicon-o-bars-3-bottom-left')
                            ->schema([
                                Forms\Components\Section::make('Footer Atelier Configuration')
                                    ->description('Manage brand credentials, telephone, emails, business hours, and compliance links.')
                                    ->schema([
                                        Forms\Components\Textarea::make('footer.brand_description')
                                            ->label('Brand Bio / Atelier Description')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('footer.phone')
                                                    ->label('Contact Phone')
                                                    ->default('+91 87912 27210'),
                                                Forms\Components\TextInput::make('footer.email')
                                                    ->label('Contact Email')
                                                    ->email()
                                                    ->default('vrazioofficail@gmail.com'),
                                                Forms\Components\TextInput::make('footer.business_hours')
                                                    ->label('Atelier Business Hours')
                                                    ->default('Monday — Saturday: 10:00 AM — 8:00 PM IST'),
                                            ]),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('footer.instagram_url')
                                                    ->label('Instagram URL')
                                                    ->url(),
                                                Forms\Components\TextInput::make('footer.facebook_url')
                                                    ->label('Facebook URL')
                                                    ->url(),
                                                Forms\Components\TextInput::make('footer.whatsapp_number')
                                                    ->label('WhatsApp Number')
                                                    ->default('+91 87912 27210'),
                                            ]),
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('footer.copyright_text')
                                                    ->label('Copyright Notice')
                                                    ->columnSpan(2),
                                                Forms\Components\TextInput::make('footer.udyam_registration')
                                                    ->label('UDYAM Registration')
                                                    ->default('UDYAM-UP-59-0089382'),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('footer.show_payment_badges')
                                                    ->label('Show Razorpay & Card Security Badges')
                                                    ->default(true),
                                                Forms\Components\Toggle::make('footer.show_bis_badge')
                                                    ->label('Show BIS Hallmark Badge')
                                                    ->default(true),
                                            ]),
                                    ]),
                            ]),

                        // Tab 11: Homepage SEO
                        Forms\Components\Tabs\Tab::make('SEO & Social')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\Section::make('Search Engine & Social Graph Optimization')
                                    ->schema([
                                        Forms\Components\TextInput::make('seo.meta_title')
                                            ->label('Page Title (<title>)')
                                            ->default('Haute Joaillerie & Master Atelier | VRAZIO Atelier')
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('seo.meta_description')
                                            ->label('Meta Description')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('seo.meta_keywords')
                                            ->label('Meta Keywords')
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('seo.og_title')
                                                    ->label('Open Graph Social Title'),
                                                Forms\Components\TextInput::make('seo.canonical_url')
                                                    ->label('Canonical URL (Leave blank for default)'),
                                            ]),
                                        Forms\Components\Textarea::make('seo.og_description')
                                            ->label('Open Graph Social Description')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Forms\Components\FileUpload::make('seo.og_image')
                                            ->label('Social Share Card Image (1200x630)')
                                            ->disk('public')
                                            ->directory('homepage/seo')
                                            ->image(),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Toggle::make('seo.enable_faq_schema')
                                                    ->label('Inject JSON-LD FAQPage Schema')
                                                    ->default(true),
                                                Forms\Components\Toggle::make('seo.enable_organization_schema')
                                                    ->label('Inject JSON-LD Organization Schema')
                                                    ->default(true),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function saveDraft(): void
    {
        $state = $this->form->getState();
        $this->service->saveDraft($state, Auth::id());

        Notification::make()
            ->title('Homepage Draft Saved')
            ->body('Draft changes saved successfully. Preview them before publishing to the live storefront.')
            ->info()
            ->send();
    }

    public function publish(): void
    {
        $state = $this->form->getState();
        $this->service->publish($state, Auth::id());

        Notification::make()
            ->title('Homepage Published to Live Storefront')
            ->body('All landing page updates have been deployed. Edge and application caches have been purged.')
            ->success()
            ->send();
    }

    public function restorePrevious(): void
    {
        $restored = $this->service->restorePrevious(Auth::id());

        if ($restored) {
            $this->mount();

            Notification::make()
                ->title('Storefront Restored to Previous Version')
                ->body('The previous published version has been restored.')
                ->warning()
                ->send();
        } else {
            Notification::make()
                ->title('No Previous Version Available')
                ->danger()
                ->send();
        }
    }
}
