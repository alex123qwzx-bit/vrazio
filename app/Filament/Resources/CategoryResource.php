<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers\SubcategoriesRelationManager;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Manage Categories';

    protected static ?string $modelLabel = 'Category';

    protected static ?string $pluralModelLabel = 'Manage Categories';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

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
                                Forms\Components\Section::make('Category Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Category Name')
                                            ->required()
                                            ->maxLength(100)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Forms\Set $set, ?string $state, string $operation): void {
                                                if ($operation === 'create' && filled($state)) {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),

                                        Forms\Components\TextInput::make('slug')
                                            ->label('URL Slug')
                                            ->required()
                                            ->maxLength(100)
                                            ->unique(ignoreRecord: true)
                                            ->alphaDash()
                                            ->helperText('Unique identifier used in URLs and storefront catalog filtering.'),

                                        Forms\Components\FileUpload::make('image')
                                            ->label('Category Image / Banner')
                                            ->image()
                                            ->directory('categories')
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('16:9')
                                            ->imageResizeTargetWidth('1200')
                                            ->imageResizeTargetHeight('675')
                                            ->columnSpanFull()
                                            ->helperText('Optional visual banner or icon for this category.'),

                                        Forms\Components\Textarea::make('description')
                                            ->label('Description')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->placeholder('Artisanal description of this master collection...'),
                                    ])
                                    ->columns(2),

                                // Inline Repeatable Subcategories (Requirement 2)
                                Forms\Components\Section::make('Subcategories')
                                    ->description('Add unlimited subcategories under this category. Example: Watches -> Analog Watches, Digital Watches, Smart Watches, Luxury Watches.')
                                    ->schema([
                                        Forms\Components\Repeater::make('subcategories')
                                            ->relationship('subcategories')
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Subcategory Name')
                                                            ->required()
                                                            ->maxLength(100)
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (Forms\Set $set, ?string $state): void {
                                                                if (filled($state)) {
                                                                    $set('slug', Str::slug($state));
                                                                }
                                                            }),

                                                        Forms\Components\TextInput::make('slug')
                                                            ->label('URL Slug')
                                                            ->required()
                                                            ->maxLength(100)
                                                            ->alphaDash()
                                                            ->helperText('Unique within this category.'),

                                                        Forms\Components\TextInput::make('sort_order')
                                                            ->label('Display Order')
                                                            ->numeric()
                                                            ->default(0),
                                                    ]),

                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('description')
                                                            ->label('Short Description')
                                                            ->placeholder('e.g. Traditional or modern silhouette...'),

                                                        Forms\Components\Toggle::make('is_active')
                                                            ->label('Active in Storefront')
                                                            ->default(true),
                                                    ]),
                                            ])
                                            ->addActionLabel('+ Add Subcategory')
                                            ->reorderable('sort_order')
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                            ->defaultItems(0)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Right column (span 1)
                        Forms\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Section::make('Display & Status')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Active in Storefront')
                                            ->default(true)
                                            ->helperText('Visible in catalog navigation and product selection.'),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Display Order')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Lower numbers display first.'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(asset('images/favicon-32x32.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subcategories_count')
                    ->counts('subcategories')
                    ->label('Subcategories')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Category $record) {
                        if ($record->products()->exists()) {
                            $action->cancel();
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot delete category')
                                ->body('This category contains products. Please reassign or delete the products first.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            SubcategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
