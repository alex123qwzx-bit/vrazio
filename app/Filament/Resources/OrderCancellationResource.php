<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderCancellationResource\Pages;
use App\Models\OrderCancellation;
use App\Models\OrderRefund;
use App\Services\CancellationRefundService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderCancellationResource extends Resource
{
    protected static ?string $model = OrderCancellation::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = 'Orders & Patrons';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'cancellation_number';

    public static function getModelLabel(): string
    {
        return 'Cancellation & Refund';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Cancellations & Refunds';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = OrderCancellation::where('status', OrderCancellation::STATUS_PENDING_REVIEW)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Docket Record')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('cancellation_number')
                        ->label('Docket #')
                        ->disabled(),

                    Forms\Components\Select::make('status')
                        ->label('Cancellation Status')
                        ->options([
                            OrderCancellation::STATUS_PENDING_REVIEW => 'Pending Review',
                            OrderCancellation::STATUS_APPROVED => 'Approved',
                            OrderCancellation::STATUS_REJECTED => 'Rejected',
                            OrderCancellation::STATUS_COMPLETED => 'Completed',
                        ])
                        ->required()
                        ->native(false),

                    Forms\Components\TextInput::make('type')
                        ->label('Scope')
                        ->formatStateUsing(fn (string $state): string => strtoupper($state) . ' CANCELLATION')
                        ->disabled(),

                    Forms\Components\TextInput::make('reason')
                        ->label('Reason Stated')
                        ->disabled(),

                    Forms\Components\Textarea::make('customer_notes')
                        ->label('Patron Notes / Comments')
                        ->disabled()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Internal Atelier Notes')
                        ->placeholder('Internal notes for order staff...')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason (if rejected)')
                        ->placeholder('Provide formal reason communicated to patron...')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Financial & Restock Status')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('refund_amount')
                        ->label('Applicable Refund (INR)')
                        ->prefix('₹')
                        ->numeric()
                        ->disabled(),

                    Forms\Components\TextInput::make('refund_method')
                        ->label('Refund Channel')
                        ->disabled(),

                    Forms\Components\Toggle::make('is_stock_restored')
                        ->label('Vault Stock Restored')
                        ->disabled(),

                    Forms\Components\Toggle::make('prive_discount_restored')
                        ->label('Privé Privilege Restored')
                        ->disabled(),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Group::make()
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    Infolists\Components\Section::make('Cancellation Docket')
                        ->columnSpan(2)
                        ->columns(2)
                        ->schema([
                            Infolists\Components\TextEntry::make('cancellation_number')
                                ->label('Docket Number')
                                ->weight('bold')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Requested Date')
                                ->dateTime('M d, Y h:i A'),

                            Infolists\Components\TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    OrderCancellation::STATUS_PENDING_REVIEW => 'warning',
                                    OrderCancellation::STATUS_APPROVED => 'info',
                                    OrderCancellation::STATUS_COMPLETED => 'success',
                                    OrderCancellation::STATUS_REJECTED => 'danger',
                                    default => 'gray',
                                }),

                            Infolists\Components\TextEntry::make('type')
                                ->label('Scope')
                                ->badge()
                                ->color(fn (string $state): string => $state === 'full' ? 'danger' : 'warning')
                                ->formatStateUsing(fn (string $state): string => strtoupper($state) . ' ORDER'),

                            Infolists\Components\TextEntry::make('reason')
                                ->label('Customer Reason')
                                ->weight('medium')
                                ->columnSpanFull(),

                            Infolists\Components\TextEntry::make('customer_notes')
                                ->label('Customer Comments')
                                ->placeholder('None provided')
                                ->columnSpanFull(),

                            Infolists\Components\TextEntry::make('admin_notes')
                                ->label('Staff Notes')
                                ->placeholder('No internal notes recorded')
                                ->columnSpanFull(),

                            Infolists\Components\TextEntry::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->visible(fn (OrderCancellation $record): bool => ! empty($record->rejection_reason))
                                ->color('danger')
                                ->weight('semibold')
                                ->columnSpanFull(),
                        ]),

                    Infolists\Components\Section::make('Order & Patron')
                        ->columnSpan(1)
                        ->schema([
                            Infolists\Components\TextEntry::make('order.order_number')
                                ->label('Order Reference')
                                ->weight('bold')
                                ->url(fn (OrderCancellation $record): ?string => $record->order ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null)
                                ->color('primary'),

                            Infolists\Components\TextEntry::make('order.client_name')
                                ->label('Patron Name'),

                            Infolists\Components\TextEntry::make('order.client_phone')
                                ->label('Phone Number')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('order.client_email')
                                ->label('Email Address')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('order.payment_status')
                                ->label('Payment Status')
                                ->badge()
                                ->color(fn (?string $state): string => match ($state) {
                                    'paid' => 'success',
                                    'pending' => 'warning',
                                    'failed' => 'danger',
                                    default => 'gray',
                                }),

                            Infolists\Components\TextEntry::make('order.fulfillment_status')
                                ->label('Fulfillment Stage')
                                ->badge(),
                        ]),
                ]),

            Infolists\Components\Section::make('Refund & Financial Summary')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('refund_amount')
                        ->label('Calculated Refund')
                        ->money('INR')
                        ->weight('bold')
                        ->color('success'),

                    Infolists\Components\TextEntry::make('refund_method')
                        ->label('Refund Method')
                        ->badge(),

                    Infolists\Components\IconEntry::make('is_stock_restored')
                        ->label('Inventory Restocked')
                        ->boolean(),

                    Infolists\Components\IconEntry::make('prive_discount_restored')
                        ->label('Privé Privilege Restored')
                        ->boolean(),
                ]),

            Infolists\Components\Section::make('Cancelled Product Piece')
                ->visible(fn (OrderCancellation $record): bool => (bool) $record->order_item_id)
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('orderItem.product_title')
                        ->label('Piece Title')
                        ->columnSpan(2),

                    Infolists\Components\TextEntry::make('orderItem.product_sku')
                        ->label('SKU')
                        ->copyable(),

                    Infolists\Components\TextEntry::make('orderItem.line_total')
                        ->label('Original Line Total')
                        ->money('INR'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cancellation_number')
                    ->label('Docket #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->url(fn (OrderCancellation $record): ?string => $record->order ? OrderResource::getUrl('view', ['record' => $record->order_id]) : null),

                Tables\Columns\TextColumn::make('order.client_name')
                    ->label('Patron')
                    ->searchable()
                    ->description(fn (OrderCancellation $record): ?string => $record->order?->client_phone)
                    ->limit(20),

                Tables\Columns\TextColumn::make('type')
                    ->label('Scope')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'full' ? 'danger' : 'warning')
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(25)
                    ->tooltip(fn (OrderCancellation $record): string => $record->reason . ($record->customer_notes ? " — Note: {$record->customer_notes}" : '')),

                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('Refund')
                    ->money('INR')
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OrderCancellation::STATUS_PENDING_REVIEW => 'warning',
                        OrderCancellation::STATUS_APPROVED => 'info',
                        OrderCancellation::STATUS_COMPLETED => 'success',
                        OrderCancellation::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('refund_method')
                    ->label('Method')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_stock_restored')
                    ->label('Restocked')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        OrderCancellation::STATUS_PENDING_REVIEW => 'Pending Review',
                        OrderCancellation::STATUS_APPROVED => 'Approved',
                        OrderCancellation::STATUS_REJECTED => 'Rejected',
                        OrderCancellation::STATUS_COMPLETED => 'Completed',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        OrderCancellation::TYPE_FULL => 'Full Order Cancellation',
                        OrderCancellation::TYPE_PARTIAL => 'Partial Item Cancellation',
                    ]),

                Tables\Filters\SelectFilter::make('refund_method')
                    ->options([
                        'razorpay' => 'Razorpay (Online)',
                        'cod' => 'Cash On Delivery',
                        'unpaid' => 'Unpaid / No Charge',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')->label('Until Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['created_until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                // 1. Approve Request
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (OrderCancellation $record): bool => $record->status === OrderCancellation::STATUS_PENDING_REVIEW)
                    ->requiresConfirmation()
                    ->modalHeading('Approve Cancellation Request')
                    ->modalDescription('Approving will release inventory back to vault, issue an official GST Credit Note, initiate online Razorpay refund, and email the customer.')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Approval Notes')
                            ->placeholder('e.g. Verified items returned to vault.'),
                    ])
                    ->action(function (OrderCancellation $record, array $data): void {
                        $service = app(CancellationRefundService::class);
                        $service->processApprovedCancellation($record, auth()->user(), $data['admin_notes'] ?? null);

                        Notification::make()
                            ->title('Cancellation Approved')
                            ->body("Docket {$record->cancellation_number} has been approved.")
                            ->success()
                            ->send();
                    }),

                // 2. Reject Request
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (OrderCancellation $record): bool => $record->status === OrderCancellation::STATUS_PENDING_REVIEW)
                    ->modalHeading('Reject Cancellation Request')
                    ->modalDescription('Provide a clear operational reason for rejection. This will be sent to the patron.')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection')
                            ->required()
                            ->placeholder('e.g., Consignment has already passed armored transit security gate.'),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Notes')
                            ->placeholder('Optional notes for staff records.'),
                    ])
                    ->action(function (OrderCancellation $record, array $data): void {
                        $service = app(CancellationRefundService::class);
                        $service->rejectCancellation($record, $data['rejection_reason'], auth()->user(), $data['admin_notes'] ?? null);

                        Notification::make()
                            ->title('Cancellation Rejected')
                            ->body("Docket {$record->cancellation_number} was rejected.")
                            ->danger()
                            ->send();
                    }),

                // 3. Execute / Retry Refund
                Tables\Actions\Action::make('execute_refund')
                    ->label('Refund')
                    ->icon('heroicon-o-banknotes')
                    ->color('primary')
                    ->visible(fn (OrderCancellation $record): bool => $record->status === OrderCancellation::STATUS_APPROVED
                        && (float) $record->refund_amount > 0
                        && ! $record->refunds()->where('status', 'refunded')->exists())
                    ->modalHeading('Execute Razorpay / Bank Refund')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Refund Notes')
                            ->placeholder('e.g. Manual payout or Razorpay re-attempt.'),
                    ])
                    ->action(function (OrderCancellation $record, array $data): void {
                        $service = app(CancellationRefundService::class);
                        $refund = $service->executeRefund($record, (float) $record->refund_amount, $data['admin_notes'] ?? null);

                        if ($refund->status === 'failed') {
                            Notification::make()
                                ->title('Refund Failed')
                                ->body($refund->failure_reason ?? 'Gateway error')
                                ->danger()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Refund Initiated')
                                ->body("Refund #{$refund->refund_number} successfully initiated.")
                                ->success()
                                ->send();
                        }
                    }),

                // 4. View Credit Note
                Tables\Actions\Action::make('credit_note')
                    ->label('Credit Note')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->visible(fn (OrderCancellation $record): bool => $record->order?->creditNotes?->isNotEmpty() ?? false)
                    ->url(fn (OrderCancellation $record): string => route('credit_note.show', $record->order->creditNotes->first()->credit_note_number))
                    ->openUrlInNewTab(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_csv')
                    ->label('Export Report (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (): StreamedResponse {
                        $cancellations = OrderCancellation::with(['order', 'orderItem', 'user', 'approvedBy', 'rejectedBy'])
                            ->latest()
                            ->get();

                        $headers = [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => 'attachment; filename="vrazio-cancellations-report-' . date('Y-m-d') . '.csv"',
                        ];

                        return response()->stream(function () use ($cancellations) {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, [
                                'Cancellation #',
                                'Order #',
                                'Order Date',
                                'Customer Name',
                                'Customer Phone',
                                'Customer Email',
                                'Scope',
                                'Cancelled SKU',
                                'Reason',
                                'Customer Notes',
                                'Status',
                                'Refund Amount (INR)',
                                'Refund Method',
                                'Stock Restored',
                                'Privé Restored',
                                'Approved By',
                                'Approved At',
                                'Rejection Reason',
                                'Created At',
                            ]);

                            foreach ($cancellations as $row) {
                                fputcsv($handle, [
                                    $row->cancellation_number,
                                    $row->order?->order_number ?? '',
                                    $row->order?->created_at?->format('Y-m-d H:i:s') ?? '',
                                    $row->order?->client_name ?? '',
                                    $row->order?->client_phone ?? '',
                                    $row->order?->client_email ?? '',
                                    $row->type,
                                    $row->orderItem?->product_sku ?? 'N/A',
                                    $row->reason,
                                    $row->customer_notes ?? '',
                                    $row->status,
                                    number_format((float) $row->refund_amount, 2, '.', ''),
                                    $row->refund_method,
                                    $row->is_stock_restored ? 'Yes' : 'No',
                                    $row->prive_discount_restored ? 'Yes' : 'No',
                                    $row->approvedBy?->name ?? '',
                                    $row->approved_at?->format('Y-m-d H:i:s') ?? '',
                                    $row->rejection_reason ?? '',
                                    $row->created_at?->format('Y-m-d H:i:s') ?? '',
                                ]);
                            }
                            fclose($handle);
                        }, 200, $headers);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderCancellations::route('/'),
            'view' => Pages\ViewOrderCancellation::route('/{record}'),
        ];
    }
}
