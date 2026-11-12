<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MembershipPlanResource\Pages;
use App\Models\MembershipPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MembershipPlanResource extends Resource
{
    protected static ?string $model = MembershipPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'VRAZIO PRIVÉ CLUB';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Section::make('Plan Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Tier Name')
                                            ->required()
                                            ->maxLength(100),

                                        Forms\Components\TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(50),

                                        Forms\Components\TextInput::make('tagline')
                                            ->label('Tagline / Subheading')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('price')
                                            ->label('Annual Membership Fee')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₹')
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('validity_months')
                                            ->label('Validity (Months)')
                                            ->required()
                                            ->integer()
                                            ->default(12)
                                            ->suffix('Months'),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('One-Time Next Order Privilege')
                                    ->description('Automatic discount unlocked on the member’s first eligible purchase.')
                                    ->schema([
                                        Forms\Components\TextInput::make('next_order_discount_percent')
                                            ->label('Discount (%)')
                                            ->required()
                                            ->numeric()
                                            ->suffix('%'),

                                        Forms\Components\TextInput::make('next_order_max_discount')
                                            ->label('Maximum Discount Cap')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₹'),

                                        Forms\Components\TextInput::make('next_order_min_cart')
                                            ->label('Minimum Cart Value')
                                            ->required()
                                            ->numeric()
                                            ->prefix('₹'),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Birthday Month Privilege')
                                    ->description('Exclusive celebration privilege during member’s registered birthday month.')
                                    ->schema([
                                        Forms\Components\TextInput::make('birthday_discount_percent')
                                            ->label('Birthday Discount (%)')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('%'),

                                        Forms\Components\TextInput::make('birthday_max_discount')
                                            ->label('Maximum Birthday Discount Cap')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('₹'),

                                        Forms\Components\TextInput::make('birthday_min_cart')
                                            ->label('Minimum Cart Value')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('₹'),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Tier Privileges & Benefits List')
                                    ->schema([
                                        Forms\Components\TagsInput::make('benefits')
                                            ->label('Key Benefits')
                                            ->placeholder('Add privilege item and press Enter')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Section::make('Visibility & Status')
                                    ->schema([
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Active / Available for Purchase')
                                            ->default(true),

                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Featured Tier (Gold Badge)')
                                            ->default(false),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Sort Order')
                                            ->integer()
                                            ->default(1),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Plan Name')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Annual Fee')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_order_discount_percent')
                    ->label('Next Order Disc.')
                    ->suffix('%')
                    ->color('success'),

                Tables\Columns\TextColumn::make('next_order_max_discount')
                    ->label('Max Cap')
                    ->money('INR'),

                Tables\Columns\TextColumn::make('birthday_discount_percent')
                    ->label('Birthday Disc.')
                    ->suffix('%')
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembershipPlans::route('/'),
            'edit' => Pages\EditMembershipPlan::route('/{record}/edit'),
        ];
    }
}
