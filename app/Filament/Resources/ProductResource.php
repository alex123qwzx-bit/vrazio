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
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                                if (filled($state)) {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),

                                        Forms\Components\TextInput::make('slug')
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

                                        Forms\Components\Select::make('category')
                                            ->required()
                                            ->options([
                                                'rings' => 'Rings',
                                                'necklaces' => 'Necklaces',
                                                'bracelets' => 'Bracelets',
                                                'earrings' => 'Earrings',
                                                'solitaires' => 'Solitaires',
                                                'couture' => 'Haute Couture & Clothes',
                                            ])
                                            ->native(false),

                                        Forms\Components\TextInput::make('metal_type')
                                            ->label('Metal / Fabric Specification')
                                            ->placeholder('e.g. 18KT Yellow Gold, Mulberry Raw Silk & Velvet')
                                            ->datalist([
                                                '18KT Yellow Gold',
                                                '18KT Rose Gold',
                                                '18KT White Gold',
                                                'Platinum 950',
                                                '22KT Gold',
                                                'Mulberry Raw Silk & Velvet',
                                                'Pure Katan Silk & 24KT Gold Zari',
                                                'Italian Micro-Velvet & Resham',
                                                'Pure Georgette & Silver Mukaish',
                                                'Silk Organza & Swarovski Crystals',
                                                'Handwoven Gold Tissue & Zari',
                                                'Matka Raw Silk & Meenakari Buttons',
                                                'Pure Changthangi Pashmina & Gold Tilla',
                                            ]),

                                        Forms\Components\TagsInput::make('tags')
                                            ->label('Product Tags')
                                            ->placeholder('Add tag (e.g. Bridal Lehenga, Zardozi, Solitaire)')
                                            ->separator(',')
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('description')
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

                                Forms\Components\Section::make('Pricing & Stock')
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₹')
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('sale_price')
                                            ->numeric()
                                            ->prefix('₹')
                                            ->nullable()
                                            ->minValue(0)
                                            ->lt('price'),

                                        Forms\Components\TextInput::make('stock_quantity')
                                            ->required()
                                            ->integer()
                                            ->minValue(0)
                                            ->default(0),

                                        Forms\Components\TextInput::make('gross_weight_grams')
                                            ->numeric()
                                            ->suffix('g')
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('net_weight_grams')
                                            ->numeric()
                                            ->suffix('g')
                                            ->minValue(0),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Certification')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Featured Product'),

                                        Forms\Components\Toggle::make('bis_hallmarked')
                                            ->label('BIS Hallmarked')
                                            ->default(true),

                                        Forms\Components\TextInput::make('certification')
                                            ->placeholder('GIA / IGI / SGL Certified')
                                            ->maxLength(255),
                                    ])
                                    ->columns(3),
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
                                            ->directory('products')
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
                    ->limit(30)
                    ->weight('bold')
                    ->tooltip(fn (Product $record): string => $record->title),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'rings' => 'success',
                        'necklaces' => 'warning',
                        'bracelets' => 'info',
                        'earrings' => 'primary',
                        'solitaires' => 'danger',
                        'couture' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('metal_type')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('price')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->money('INR')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    })
                    ->badge(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\IconColumn::make('bis_hallmarked')
                    ->boolean()
                    ->label('Hallmarked'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'rings' => 'Rings',
                        'necklaces' => 'Necklaces',
                        'bracelets' => 'Bracelets',
                        'earrings' => 'Earrings',
                        'solitaires' => 'Solitaires',
                        'couture' => 'Haute Couture & Clothes',
                    ]),

                Tables\Filters\SelectFilter::make('metal_type')
                    ->options([
                        '18KT Yellow Gold' => '18KT Yellow Gold',
                        '18KT Rose Gold' => '18KT Rose Gold',
                        '18KT White Gold' => '18KT White Gold',
                        'Platinum 950' => 'Platinum 950',
                        '22KT Gold' => '22KT Gold',
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

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
