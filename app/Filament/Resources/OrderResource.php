<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Orders & Patrons';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Infolists\Components\Section::make('Order Details')
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('order_number')
                                    ->label('Order Number')
                                    ->weight('bold')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Order Date')
                                    ->dateTime(),

                                Infolists\Components\TextEntry::make('payment_status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),

                                Infolists\Components\TextEntry::make('fulfillment_status')
                                    ->label('Delivery Status')
                                    ->badge()
                                    ->color(fn (Order $record): string => match ($record->getCasualStatusKey()) {
                                        'order_placed' => 'gray',
                                        'packed' => 'warning',
                                        'shipped' => 'info',
                                        'out_for_delivery' => 'primary',
                                        'delivered' => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (Order $record): string => $record->getCasualStatusLabel()),

                                Infolists\Components\TextEntry::make('payment_gateway')
                                    ->label('Payment Gateway')
                                    ->placeholder('—'),

                                Infolists\Components\TextEntry::make('razorpay_order_id')
                                    ->label('Razorpay Order ID')
                                    ->copyable()
                                    ->placeholder('—'),

                                Infolists\Components\TextEntry::make('razorpay_payment_id')
                                    ->label('Razorpay Payment ID')
                                    ->copyable()
                                    ->placeholder('—'),
                            ]),

                        Infolists\Components\Section::make('Client Information')
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('client_name')
                                    ->label('Client Name'),

                                Infolists\Components\TextEntry::make('client_email')
                                    ->label('Email')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('client_phone')
                                    ->label('Phone')
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('otp_verified_at')
                                    ->label('OTP Verified At')
                                    ->dateTime()
                                    ->placeholder('Not verified'),
                            ]),

                        Infolists\Components\Section::make('Shipping Address')
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('shipping_address.street')
                                    ->label('Street'),

                                Infolists\Components\TextEntry::make('shipping_address.city')
                                    ->label('City'),

                                Infolists\Components\TextEntry::make('shipping_address.state')
                                    ->label('State'),

                                Infolists\Components\TextEntry::make('shipping_address.pincode')
                                    ->label('Pincode'),
                            ]),

                        Infolists\Components\Section::make('Delivery & Tracking')
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('tracking_courier')
                                    ->label('Courier Partner')
                                    ->placeholder('Not assigned yet'),

                                Infolists\Components\TextEntry::make('tracking_number')
                                    ->label('Tracking # / AWB')
                                    ->copyable()
                                    ->placeholder('—'),

                                Infolists\Components\TextEntry::make('tracking_notes')
                                    ->label('Delivery Notes')
                                    ->columnSpanFull()
                                    ->placeholder('—'),

                                Infolists\Components\TextEntry::make('estimated_delivery_at')
                                    ->label('Expected Delivery Date')
                                    ->date('D, d M Y')
                                    ->placeholder('Not set'),

                                Infolists\Components\TextEntry::make('delivery_time_slot')
                                    ->label('Delivery Time Window')
                                    ->placeholder('Anytime'),

                                Infolists\Components\TextEntry::make('packed_at')
                                    ->label('Packed At')
                                    ->dateTime()
                                    ->placeholder('—'),

                                Infolists\Components\TextEntry::make('shipped_at')
                                    ->label('Shipped At')
                                    ->dateTime()
                                    ->placeholder('—'),

                                Infolists\Components\TextEntry::make('delivered_at')
                                    ->label('Delivered At')
                                    ->dateTime()
                                    ->placeholder('—'),
                            ]),

                        Infolists\Components\Section::make('Financial Summary')
                            ->columns(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('subtotal')
                                    ->money('INR'),

                                Infolists\Components\TextEntry::make('tax_gst')
                                    ->label('GST')
                                    ->money('INR'),

                                Infolists\Components\TextEntry::make('shipping_cost')
                                    ->money('INR'),

                                Infolists\Components\TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money('INR')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                            ]),

                        Infolists\Components\Section::make('Order Items')
                            ->columnSpanFull()
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('items')
                                    ->columns(6)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('product_title')
                                            ->label('Product'),

                                        Infolists\Components\TextEntry::make('product_sku')
                                            ->label('SKU'),

                                        Infolists\Components\TextEntry::make('size')
                                            ->label('Size / Fit')
                                            ->badge()
                                            ->placeholder('—'),

                                        Infolists\Components\TextEntry::make('unit_price')
                                            ->label('Unit Price')
                                            ->money('INR'),

                                        Infolists\Components\TextEntry::make('quantity')
                                            ->label('Qty'),

                                        Infolists\Components\TextEntry::make('line_total')
                                            ->label('Line Total')
                                            ->money('INR')
                                            ->weight('bold'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Order number copied'),

                Tables\Columns\TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable(),

                Tables\Columns\TextColumn::make('client_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('client_phone')
                    ->label('Phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('INR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_gateway')
                    ->label('Payment Method')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'cod' => 'warning',
                        'razorpay' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cod' => 'Cash on Delivery (COD)',
                        'razorpay' => 'Razorpay (Online)',
                        default => $state ? strtoupper($state) : '—',
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('fulfillment_status')
                    ->label('Delivery Status')
                    ->badge()
                    ->color(fn (Order $record): string => match ($record->getCasualStatusKey()) {
                        'order_placed' => 'gray',
                        'packed' => 'warning',
                        'shipped' => 'info',
                        'out_for_delivery' => 'primary',
                        'delivered' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (Order $record): string => $record->getCasualStatusLabel()),

                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Tracking #')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\SelectFilter::make('fulfillment_status')
                    ->label('Delivery Status')
                    ->options([
                        'order_placed' => 'Order Placed',
                        'packed' => 'Packed',
                        'shipped' => 'Shipped',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered' => 'Delivered',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($q, $date) => $q->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($q, $date) => $q->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('update_delivery')
                    ->label('Update Delivery')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->modalHeading(fn (Order $record): string => 'Update Delivery Status — ' . $record->order_number)
                    ->modalDescription('Update order status and courier tracking info for the customer.')
                    ->modalSubmitActionLabel('Save Updates')
                    ->form([
                        Forms\Components\Radio::make('fulfillment_status')
                            ->label('Order Delivery Stage')
                            ->required()
                            ->options([
                                'order_placed' => '1. Order Placed (Confirmed)',
                                'packed' => '2. Packed (In box & sealed)',
                                'shipped' => '3. Shipped (Handed over to courier)',
                                'out_for_delivery' => '4. Out for Delivery (Arrived in customer city)',
                                'delivered' => '5. Delivered (Delivered to customer)',
                            ])
                            ->default(fn (Order $record): string => $record->getCasualStatusKey()),

                        Forms\Components\TextInput::make('tracking_courier')
                            ->label('Courier Partner')
                            ->placeholder('e.g. Blue Dart, Delhivery, DTDC, India Post')
                            ->default(fn (Order $record): ?string => $record->tracking_courier),

                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Tracking Number / AWB')
                            ->placeholder('e.g. BD987654321IN')
                            ->default(fn (Order $record): ?string => $record->tracking_number),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('estimated_delivery_at')
                                    ->label('Estimated Delivery Date')
                                    ->placeholder('Select expected date')
                                    ->default(fn (Order $record): ?string => $record->estimated_delivery_at?->format('Y-m-d')),

                                Forms\Components\Select::make('delivery_time_slot')
                                    ->label('Delivery Time Window')
                                    ->placeholder('Select or type delivery window')
                                    ->options([
                                        'Anytime (9:00 AM - 8:00 PM)' => 'Anytime (9:00 AM - 8:00 PM)',
                                        'Morning (9:00 AM - 1:00 PM)' => 'Morning (9:00 AM - 1:00 PM)',
                                        'Afternoon (1:00 PM - 5:00 PM)' => 'Afternoon (1:00 PM - 5:00 PM)',
                                        'Evening (5:00 PM - 9:00 PM)' => 'Evening (5:00 PM - 9:00 PM)',
                                        'By End of Day (6:00 PM)' => 'By End of Day (6:00 PM)',
                                    ])
                                    ->searchable()
                                    ->default(fn (Order $record): ?string => $record->delivery_time_slot),
                            ]),

                        Forms\Components\Textarea::make('tracking_notes')
                            ->label('Delivery Update Note')
                            ->placeholder('e.g. Package arrived at local city hub, out for delivery today')
                            ->default(fn (Order $record): ?string => $record->tracking_notes)
                            ->rows(2),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->updateTrackingStatus(
                            newStatus: $data['fulfillment_status'],
                            courier: $data['tracking_courier'] ?? null,
                            trackingNumber: $data['tracking_number'] ?? null,
                            notes: $data['tracking_notes'] ?? null,
                            estimatedDeliveryAt: $data['estimated_delivery_at'] ?? null,
                            deliveryTimeSlot: $data['delivery_time_slot'] ?? null,
                        );

                        Notification::make()
                            ->title('Delivery Status Updated')
                            ->body(sprintf('Order %s is now marked as "%s".', $record->order_number, $record->getCasualStatusLabel()))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('advance_status')
                    ->label('Next Step &rarr;')
                    ->icon('heroicon-o-arrow-right')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Advance Delivery Step')
                    ->modalDescription(fn (Order $record): string => sprintf(
                        'Advance order %s from "%s" to next step?',
                        $record->order_number,
                        $record->getCasualStatusLabel()
                    ))
                    ->visible(fn (Order $record): bool => $record->getCasualStatusKey() !== 'delivered')
                    ->action(function (Order $record): void {
                        $record->advanceFulfillment();

                        Notification::make()
                            ->title('Delivery Advanced')
                            ->body(sprintf('Order %s is now "%s".', $record->order_number, $record->getCasualStatusLabel()))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->payment_status !== 'paid')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Payment Receipt')
                    ->modalDescription(fn (Order $record): string => sprintf(
                        'Confirm that ₹%s has been collected for Order %s (%s)?',
                        number_format((float) $record->total_amount, 2),
                        $record->order_number,
                        $record->payment_gateway === 'cod' ? 'Cash on Delivery' : 'Online Payment'
                    ))
                    ->action(function (Order $record): void {
                        $record->update(['payment_status' => 'paid']);

                        Notification::make()
                            ->title('Payment Recorded')
                            ->body(sprintf('Order %s has been marked as Paid.', $record->order_number))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->url(fn (Order $record): string => route('order.invoice', $record->order_number))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    protected static function getNextFulfillmentStatus(string $currentStatus): string
    {
        return match ($currentStatus) {
            'inscribed' => 'hallmarking',
            'hallmarking' => 'armoured_transit',
            'armoured_transit' => 'delivered',
            default => $currentStatus,
        };
    }

    protected static function formatFulfillmentStatus(string $status): string
    {
        return match ($status) {
            'inscribed' => 'Inscribed',
            'hallmarking' => 'Hallmarking',
            'armoured_transit' => 'Armoured Transit',
            'delivered' => 'Delivered',
            default => ucfirst($status),
        };
    }
}
