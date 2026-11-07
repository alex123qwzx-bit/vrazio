<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\WarrantyClaimResource\Pages;
use App\Models\WarrantyClaim;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WarrantyClaimResource extends Resource
{
    protected static ?string $model = WarrantyClaim::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Orders & Patrons';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'claim_number';

    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Display live count of pending/un-reviewed warranty claims as navigation badge.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = WarrantyClaim::whereIn('status', ['submitted', 'under_review'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Warranty Claim Management')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Claim Status')
                        ->required()
                        ->options(WarrantyClaim::STATUSES)
                        ->native(false),

                    Forms\Components\Toggle::make('is_checked')
                        ->label('Mark as Concierge Checked')
                        ->helperText('Enable to mark this claim as examined by an atelier specialist.')
                        ->default(false),

                    Forms\Components\Textarea::make('admin_note')
                        ->label('Atelier Note (Visible to Patron)')
                        ->placeholder('e.g. Our master jeweller has approved complimentary prong tightening and sonic cleaning. Complimentary armored transit docket dispatched.')
                        ->columnSpanFull()
                        ->rows(4),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('claim_number')
                    ->label('Claim Number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Claim number copied'),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order Ref')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->url(fn (WarrantyClaim $record): ?string => $record->order ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Patron')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('Piece / Garment')
                    ->limit(25)
                    ->tooltip(fn (WarrantyClaim $record): ?string => $record->product_name),

                Tables\Columns\TextColumn::make('issue_type')
                    ->label('Issue Category')
                    ->formatStateUsing(fn (string $state): string => WarrantyClaim::ISSUE_TYPES[$state] ?? ucfirst(str_replace('_', ' ', $state)))
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'gray',
                        'under_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'resolved' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_checked')
                    ->label('Checked')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Filed On')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(WarrantyClaim::STATUSES),

                Tables\Filters\SelectFilter::make('issue_type')
                    ->options(WarrantyClaim::ISSUE_TYPES),

                Tables\Filters\TernaryFilter::make('is_checked')
                    ->label('Atelier Checked'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // Quick Action: Mark as Checked
                Tables\Actions\Action::make('mark_as_checked')
                    ->label('Check')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (WarrantyClaim $record): bool => ! $record->is_checked)
                    ->action(function (WarrantyClaim $record): void {
                        $record->markAsChecked(auth()->id());

                        Notification::make()
                            ->title('Claim Checked')
                            ->body("Claim {$record->claim_number} is now verified by atelier staff.")
                            ->success()
                            ->send();
                    }),

                // Quick Action: Update Status & Admin Note Modal
                Tables\Actions\Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->required()
                            ->options(WarrantyClaim::STATUSES)
                            ->default(fn (WarrantyClaim $record): string => $record->status),

                        Forms\Components\Textarea::make('admin_note')
                            ->label('Admin Note to Customer')
                            ->default(fn (WarrantyClaim $record): ?string => $record->admin_note)
                            ->rows(3),
                    ])
                    ->action(function (WarrantyClaim $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                            'admin_note' => $data['admin_note'],
                            'is_checked' => true,
                            'checked_by' => auth()->id() ?? $record->checked_by,
                            'checked_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Status Updated')
                            ->body("Warranty claim {$record->claim_number} status changed to " . ucfirst(str_replace('_', ' ', $data['status'])))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Grid::make(3)
                    ->schema([
                        // Left Dossier (span 2)
                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Warranty Claim Dossier')
                                ->schema([
                                    Infolists\Components\TextEntry::make('claim_number')
                                        ->label('Claim Number')
                                        ->weight('bold')
                                        ->copyable(),

                                    Infolists\Components\TextEntry::make('order.order_number')
                                        ->label('Order Reference')
                                        ->copyable()
                                        ->url(fn (WarrantyClaim $record): ?string => $record->order ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null),

                                    Infolists\Components\TextEntry::make('product_name')
                                        ->label('Product / Piece Title'),

                                    Infolists\Components\TextEntry::make('issue_type')
                                        ->label('Issue Category')
                                        ->formatStateUsing(fn (string $state): string => WarrantyClaim::ISSUE_TYPES[$state] ?? $state)
                                        ->badge()
                                        ->color('primary'),

                                    Infolists\Components\TextEntry::make('created_at')
                                        ->label('Date Filed')
                                        ->dateTime('F d, Y — h:i A'),

                                    Infolists\Components\TextEntry::make('status')
                                        ->label('Current Status')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'submitted' => 'gray',
                                            'under_review' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'resolved' => 'info',
                                            default => 'gray',
                                        }),

                                    Infolists\Components\TextEntry::make('description')
                                        ->label('Detailed Defect Description')
                                        ->columnSpanFull()
                                        ->prose(),
                                ])
                                ->columns(2),

                            Infolists\Components\Section::make('Photographic Evidence')
                                ->schema([
                                    Infolists\Components\RepeatableEntry::make('images')
                                        ->label('Evidence Images')
                                        ->schema([
                                            Infolists\Components\ImageEntry::make('url')
                                                ->label('')
                                                ->height(200)
                                                ->square(),
                                        ])
                                        ->grid(3)
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->columnSpan(2),

                        // Right Column (span 1)
                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Patron Contact Info')
                                ->schema([
                                    Infolists\Components\TextEntry::make('customer_name')
                                        ->label('Client Name')
                                        ->weight('bold'),

                                    Infolists\Components\TextEntry::make('phone')
                                        ->label('Telephone')
                                        ->copyable(),

                                    Infolists\Components\TextEntry::make('email')
                                        ->label('Email Address')
                                        ->copyable(),
                                ]),

                            Infolists\Components\Section::make('Concierge Verification')
                                ->schema([
                                    Infolists\Components\IconEntry::make('is_checked')
                                        ->label('Checked by Atelier')
                                        ->boolean(),

                                    Infolists\Components\TextEntry::make('checkedByUser.name')
                                        ->label('Verified By')
                                        ->placeholder('Pending review'),

                                    Infolists\Components\TextEntry::make('checked_at')
                                        ->label('Verified Timestamp')
                                        ->dateTime('M d, Y h:i A')
                                        ->placeholder('Not checked yet'),

                                    Infolists\Components\TextEntry::make('admin_note')
                                        ->label('Customer-Facing Atelier Note')
                                        ->placeholder('No notes provided yet')
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->columnSpan(1),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarrantyClaims::route('/'),
            'view' => Pages\ViewWarrantyClaim::route('/{record}'),
            'edit' => Pages\EditWarrantyClaim::route('/{record}/edit'),
        ];
    }
}
