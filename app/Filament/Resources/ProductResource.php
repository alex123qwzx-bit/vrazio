<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Left column (span 2)
                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Section::make('Product Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('Product Title / Name')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                                if (filled($state)) {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),

                                        Forms\Components\TextInput::make('slug')
                                            ->label('URL Slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->alphaDash(),

                                        Forms\Components\TextInput::make('sku')
                                            ->label('SKU')
                                            ->required()
                                            ->maxLength(50)
                                            ->unique(ignoreRecord: true)
                                            ->prefix('VRZ-'),

                                        // Requirement 2: The 4 Authoritative Categories
                                        Forms\Components\Select::make('category')
                                            ->label('Product Category')
                                            ->required()
                                            ->options(Product::CATEGORIES)
                                            ->default(Product::CATEGORY_JEWELRY)
                                            ->native(false)
                                            ->live()
                                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                                // Default sensible GST rate per category
                                                if ($state === 'jewelry') {
                                                    $set('gst_percent', 3);
                                                } elseif ($state === 'clothing') {
                                                    $set('gst_percent', 12);
                                                } elseif ($state === 'watches' || $state === 'footwear') {
                                                    $set('gst_percent', 18);
                                                }
                                            })
                                            ->helperText('Authoritative master category for storefront navigation and catalog.'),

                                        Forms\Components\TextInput::make('metal_type')
                                            ->label('Material / Metal Specification')
                                            ->placeholder('e.g. 18KT Yellow Gold, Mulberry Raw Silk, Italian Calfskin')
                                            ->datalist([
                                                '18KT Yellow Gold',
                                                '18KT Rose Gold',
                                                '18KT White Gold',
                                                'Platinum 950',
                                                '22KT Gold',
                                                'Mulberry Raw Silk & Velvet',
                                                'Pure Katan Silk & 24KT Gold Zari',
                                                'Italian Micro-Velvet & Resham',
                                                'Swiss Automatic Movement & Sapphire Crystal',
                                                'Japanese Chronograph Movement & Ceramic Bezel',
                                                'Italian Full-Grain Calfskin Leather',
                                                'Handcrafted Embroidered Velvet & Leather Sole',
                                            ]),

                                        Forms\Components\TagsInput::make('tags')
                                            ->label('Product Tags')
                                            ->placeholder('Add tag (e.g. Solitaire, Chronograph, Bridal, Oxford)')
                                            ->separator(',')
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('description')
                                            ->label('Product Description')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'link',
                                                'undo',
                                                'redo',
                                            ]),
                                    ])
                                    ->columns(2),

                                // Requirements 3, 4, 6 & 7: Base Price, GST, Discount & Live Price Preview
                                Forms\Components\Section::make('Pricing, GST & Dynamic Calculation')
                                    ->description('Base price is entered before GST. The final GST-inclusive price is calculated dynamically.')
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Base Price (Pre-GST)')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₹')
                                            ->minValue(0)
                                            ->live(onBlur: true)
                                            ->helperText('Taxable wholesale/base valuation before GST and discounts.'),

                                        Forms\Components\Select::make('gst_percent')
                                            ->label('GST Percentage')
                                            ->required()
                                            ->options(Product::GST_RATES)
                                            ->default(3)
                                            ->native(false)
                                            ->live()
                                            ->helperText('Manually selectable GST percentage applicable to this product.'),

                                        Forms\Components\TextInput::make('discount_percent')
                                            ->label('Discount Percentage (%)')
                                            ->numeric()
                                            ->suffix('%')
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->helperText('Set 0% for regular price, or enter discount (e.g. 20 for 20% OFF).'),

                                        Forms\Components\TextInput::make('stock_quantity')
                                            ->label('Stock / Vault Quantity')
                                            ->required()
                                            ->integer()
                                            ->minValue(0)
                                            ->default(0),

                                        // Live Price Calculation Preview
                                        Forms\Components\Placeholder::make('live_price_preview')
                                            ->label('Live Price Calculation Preview')
                                            ->content(function (Forms\Get $get): HtmlString {
                                                $basePrice = (float) ($get('price') ?? 0);
                                                $discountPercent = (float) ($get('discount_percent') ?? 0);
                                                $gstPercent = (float) ($get('gst_percent') ?? 0);
                                                $category = Product::CATEGORIES[$get('category')] ?? ucfirst((string) $get('category'));

                                                $discountAmount = round($basePrice * ($discountPercent / 100.0), 2);
                                                $discountedPrice = max(0.0, round($basePrice - $discountAmount, 2));
                                                $gstAmount = round($discountedPrice * ($gstPercent / 100.0), 2);
                                                $finalPrice = round($discountedPrice + $gstAmount, 2);

                                                $badgeHtml = $discountPercent > 0
                                                    ? "<span style='display:inline-block; background-color:#9B1B30; color:#fff; font-weight:700; font-size:11px; padding:2px 8px; border-radius:3px; margin-right:8px;'>[{$discountPercent}% OFF]</span>"
                                                    : "<span style='color:#6b7280; font-size:11px; margin-right:8px;'>No discount</span>";

                                                return new HtmlString("
                                                    <div style='background:#fdfbf7; border:1px solid rgba(212,175,55,0.35); border-radius:8px; padding:16px; font-family:inherit;'>
                                                        <div style='display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-bottom:14px; font-size:12px;'>
                                                            <div style='background:#fff; padding:8px 12px; border:1px solid #e5e7eb; border-radius:4px;'>
                                                                <span style='color:#6b7280; display:block; font-size:10px; text-transform:uppercase;'>Authoritative Category</span>
                                                                <strong style='color:#111827; font-size:13px;'>{$category}</strong>
                                                            </div>
                                                            <div style='background:#fff; padding:8px 12px; border:1px solid #e5e7eb; border-radius:4px;'>
                                                                <span style='color:#6b7280; display:block; font-size:10px; text-transform:uppercase;'>Base Price</span>
                                                                <strong style='font-family:monospace; font-size:13px;'>₹" . number_format($basePrice, 2) . "</strong>
                                                            </div>
                                                            <div style='background:#fff; padding:8px 12px; border:1px solid #e5e7eb; border-radius:4px;'>
                                                                <span style='color:#6b7280; display:block; font-size:10px; text-transform:uppercase;'>Discount ({$discountPercent}%)</span>
                                                                <strong style='color:#dc2626; font-family:monospace; font-size:13px;'>- ₹" . number_format($discountAmount, 2) . "</strong>
                                                            </div>
                                                            <div style='background:#fff; padding:8px 12px; border:1px solid #e5e7eb; border-radius:4px;'>
                                                                <span style='color:#6b7280; display:block; font-size:10px; text-transform:uppercase;'>Discounted Taxable Price</span>
                                                                <strong style='font-family:monospace; font-size:13px;'>₹" . number_format($discountedPrice, 2) . "</strong>
                                                            </div>
                                                            <div style='background:#fff; padding:8px 12px; border:1px solid #e5e7eb; border-radius:4px;'>
                                                                <span style='color:#6b7280; display:block; font-size:10px; text-transform:uppercase;'>GST ({$gstPercent}%)</span>
                                                                <strong style='color:#059669; font-family:monospace; font-size:13px;'>+ ₹" . number_format($gstAmount, 2) . "</strong>
                                                            </div>
                                                        </div>

                                                        <div style='background:#0E261C; color:#fff; padding:12px 16px; border-radius:6px; display:flex; justify-content:space-between; align-items:center;'>
                                                            <div>
                                                                <span style='color:#D4AF37; font-size:10px; text-transform:uppercase; letter-spacing:0.1em; display:block;'>Calculated Customer Price (GST Inclusive)</span>
                                                                <div style='display:flex; align-items:center; margin-top:2px;'>
                                                                    {$badgeHtml}
                                                                    <span style='font-family:monospace; font-size:18px; font-weight:700; color:#D4AF37;'>₹" . number_format($finalPrice, 2) . "</span>
                                                                </div>
                                                            </div>
                                                            <div style='text-align:right;'>
                                                                <span style='color:#9ca3af; font-size:10px; display:block;'>Storefront Customer Display</span>
                                                                <span style='color:#fff; font-weight:700; font-size:14px; font-family:monospace;'>" . ($discountPercent > 0 ? "[{$discountPercent}% OFF] " : "") . "₹" . number_format($finalPrice, 0) . "</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                ");
                                            })
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                // VRAZIO PRIVÉ CLUB & Lifetime Warranty Configuration
                                Forms\Components\Section::make('Warranty & Guarantee Privileges')
                                    ->description('Eligible active VRAZIO PRIVÉ CLUB members can submit claims on this piece.')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_warranty_eligible')
                                            ->label('Warranty Claim Eligible')
                                            ->default(true)
                                            ->helperText('Enables members-only warranty claims for active PRIVÉ CLUB patrons.'),

                                        Forms\Components\TextInput::make('warranty_period_months')
                                            ->label('Warranty Period (Months)')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(12)
                                            ->suffix('Months'),

                                        Forms\Components\Textarea::make('warranty_coverage')
                                            ->label('Warranty Coverage')
                                            ->rows(2)
                                            ->placeholder('e.g. Prong tightening, ultrasonic cleaning, movement calibration, hallmarked purity guarantee.')
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('warranty_exclusions')
                                            ->label('Warranty Exclusions')
                                            ->rows(2)
                                            ->placeholder('e.g. Accidental fractures, chemical exposure, unauthorized third-party intervention.')
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('warranty_remedies')
                                            ->label('Atelier Remedies')
                                            ->rows(2)
                                            ->placeholder('e.g. Complimentary repair, master vault refurbishing, or replacement upon assay inspection.')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Weight & Specifications')
                                    ->schema([
                                        Forms\Components\TextInput::make('gross_weight_grams')
                                            ->label('Gross Weight (g)')
                                            ->numeric()
                                            ->suffix('g')
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('net_weight_grams')
                                            ->label('Net Gold / Metal Weight (g)')
                                            ->numeric()
                                            ->suffix('g')
                                            ->minValue(0),

                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Featured in Curations'),

                                        Forms\Components\Toggle::make('bis_hallmarked')
                                            ->label('BIS Hallmarked')
                                            ->default(true),

                                        Forms\Components\TextInput::make('certification')
                                            ->label('Assay / Gemological Certification')
                                            ->placeholder('GIA / IGI / SGL Certified')
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),

                        // Right column
                        Forms\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Section::make('Product Images')
                                    ->schema([
                                        Forms\Components\FileUpload::make('images')
                                            ->multiple()
                                            ->image()
                                            ->disk('public')
                                            ->directory('products')
                                            ->visibility('public')
                                            ->maxFiles(6)
                                            ->reorderable()
                                            ->imageEditor()
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Technical Specifications')
                                    ->schema([
                                        Forms\Components\KeyValue::make('specs')
                                            ->addable()
                                            ->deletable()
                                            ->reorderable()
                                            ->keyLabel('Property')
                                            ->valueLabel('Value')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Image')
                    ->circular()
                    ->size(40)
                    ->stacked()
                    ->limit(1),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('SKU copied to clipboard'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(26)
                    ->weight('bold')
                    ->tooltip(fn (Product $record): string => $record->title),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Product::CATEGORIES[$state] ?? ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'jewelry' => 'success',
                        'clothing' => 'warning',
                        'watches' => 'info',
                        'footwear' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Base Price')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gst_percent')
                    ->label('GST %')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_percent')
                    ->label('Discount')
                    ->suffix('%')
                    ->color(fn ($state) => (float)$state > 0 ? 'danger' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('Final Price')
                    ->money('INR')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    })
                    ->badge(),

                Tables\Columns\IconColumn::make('is_warranty_eligible')
                    ->boolean()
                    ->label('Warranty'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->options(Product::CATEGORIES),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Tables\Filters\TernaryFilter::make('is_warranty_eligible')
                    ->label('Warranty Eligible'),

                Tables\Filters\TernaryFilter::make('bis_hallmarked')
                    ->label('BIS Hallmarked'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
