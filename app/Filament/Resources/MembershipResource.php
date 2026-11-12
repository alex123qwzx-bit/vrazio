<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MembershipResource\Pages;
use App\Models\Membership;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MembershipResource extends Resource
{
    protected static ?string $model = Membership::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'VRAZIO PRIVÉ CLUB';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'member_id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Patron & Membership Credentials')
                    ->schema([
                        Forms\Components\TextInput::make('member_id')
                            ->label('Member ID')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('user_id')
                            ->label('Patron Account')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\Select::make('membership_plan_id')
                            ->label('Membership Plan')
                            ->relationship('plan', 'name')
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\Select::make('status')
                            ->label('Membership Status')
                            ->options([
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'suspended' => 'Suspended',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Commencement Date')
                            ->required(),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiration Date')
                            ->required(),

                        Forms\Components\Toggle::make('next_order_discount_used')
                            ->label('One-Time Next Order Discount Used'),

                        Forms\Components\TextInput::make('fee_paid')
                            ->label('Fee Paid')
                            ->numeric()
                            ->prefix('₹')
                            ->disabledOn('edit'),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Atelier / Admin Notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_id')
                    ->label('Member ID')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Patron')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tier_name')
                    ->label('Tier')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'member' => 'success',
                        'gold member' => 'warning',
                        'gold max member' => 'danger',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'suspended' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('next_order_discount_used')
                    ->label('Disc. Used')
                    ->boolean(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Valid Until')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tier')
                    ->options([
                        'member' => 'Member',
                        'gold' => 'Gold Member',
                        'gold_max' => 'Gold Max Member',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'suspended' => 'Suspended',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('verify_card')
                    ->label('Digital Card')
                    ->icon('heroicon-o-qr-code')
                    ->url(fn (Membership $record): string => route('prive-club.verify', $record->member_id))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Membership $record): bool => $record->status === 'active')
                    ->action(function (Membership $record): void {
                        $record->update(['status' => 'suspended']);
                        Notification::make()
                            ->title("Membership {$record->member_id} Suspended")
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Membership $record): bool => $record->status === 'suspended')
                    ->action(function (Membership $record): void {
                        $record->update(['status' => 'active']);
                        Notification::make()
                            ->title("Membership {$record->member_id} Reactivated")
                            ->success()
                            ->send();
                    }),

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
            'index' => Pages\ListMemberships::route('/'),
            'edit' => Pages\EditMembership::route('/{record}/edit'),
        ];
    }
}
