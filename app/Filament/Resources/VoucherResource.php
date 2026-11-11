<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Section::make('Voucher Credentials & Value')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('code')
                                                    ->label('Voucher Code')
                                                    ->required()
                                                    ->maxLength(50)
                                                    ->unique(ignoreRecord: true)
                                                    ->placeholder('e.g. ROYAL10')
                                                    ->dehydrateStateUsing(fn ($state) => strtoupper(trim((string)$state)))
                                                    ->helperText('Patrons enter this code in cart or checkout.'),

                                                Forms\Components\Select::make('discount_type')
                                                    ->label('Discount Type')
                                                    ->options([
                                                        'percentage' => 'Percentage (%)',
                                                        'fixed' => 'Fixed Amount (₹)',
                                                    ])
                                                    ->default('percentage')
                                                    ->required()
                                                    ->live(),
                                            ]),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('discount_value')
                                                    ->label('Discount Value')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix(fn (Forms\Get $get) => $get('discount_type') === 'fixed' ? '₹' : '')
                                                    ->suffix(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? '%' : '')
                                                    ->helperText(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? 'e.g. 10 for 10% off' : 'e.g. 500 for ₹500 off'),

                                                Forms\Components\TextInput::make('min_spend')
                                                    ->label('Minimum Spend')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->prefix('₹')
                                                    ->helperText('Minimum cart subtotal to unlock voucher.'),

                                                Forms\Components\TextInput::make('max_discount')
                                                    ->label('Max Discount Cap')
                                                    ->numeric()
                                                    ->prefix('₹')
                                                    ->nullable()
                                                    ->helperText('Optional ceiling cap for percentage discounts.'),
                                            ]),

                                        Forms\Components\Textarea::make('description')
                                            ->label('Description / Marketing Note')
                                            ->placeholder('e.g. Exclusive 10% welcome privilege for verified patrons')
                                            ->rows(2),

                                        Forms\Components\Select::make('product_id')
                                            ->label('Product Exclusivity (Optional)')
                                            ->relationship('product', 'title')
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->helperText('Leave empty for storewide validity across all atelier collections, or select a specific piece.'),
                                    ]),
                            ]),

                        Forms\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Section::make('Availability & Limits')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Voucher Active')
                                            ->default(true)
                                            ->helperText('Deactivate to immediately suspend voucher redemptions.'),

                                        Forms\Components\TextInput::make('usage_limit')
                                            ->label('Usage Limit')
                                            ->numeric()
                                            ->nullable()
                                            ->placeholder('Unlimited')
                                            ->helperText('Leave blank for unlimited patron redemptions.'),

                                        Forms\Components\TextInput::make('used_count')
                                            ->label('Times Redeemed')
                                            ->numeric()
                                            ->disabled()
                                            ->default(0),

                                        Forms\Components\DateTimePicker::make('valid_from')
                                            ->label('Valid From')
                                            ->nullable()
                                            ->seconds(false),

                                        Forms\Components\DateTimePicker::make('valid_until')
                                            ->label('Valid Until')
                                            ->nullable()
                                            ->seconds(false),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('discount_label')
                    ->label('Discount')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('product.title')
                    ->label('Designated Piece')
                    ->placeholder('Storewide (All Pieces)')
                    ->limit(25),

                Tables\Columns\TextColumn::make('min_spend')
                    ->label('Min Spend')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('usage')
                    ->label('Usage')
                    ->getStateUsing(fn (Voucher $record): string => $record->usage_limit ? "{$record->used_count} / {$record->usage_limit}" : "{$record->used_count} (unlimited)"),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Expires At')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('Never')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
