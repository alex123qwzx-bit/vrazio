<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('update_delivery')
                ->label('Update Delivery Status')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->modalHeading(fn (): string => 'Update Delivery Status — ' . $this->record->order_number)
                ->modalDescription('Choose delivery stage and enter courier tracking info.')
                ->modalSubmitActionLabel('Save Status')
                ->form([
                    \Filament\Forms\Components\Radio::make('fulfillment_status')
                        ->label('Order Delivery Stage')
                        ->required()
                        ->options([
                            'order_placed' => '1. Order Placed (Confirmed)',
                            'packed' => '2. Packed (In box & sealed)',
                            'shipped' => '3. Shipped (Handed over to courier)',
                            'out_for_delivery' => '4. Out for Delivery (Arrived in customer city)',
                            'delivered' => '5. Delivered (Delivered to customer)',
                        ])
                        ->default(fn (): string => $this->record->getCasualStatusKey()),

                    \Filament\Forms\Components\TextInput::make('tracking_courier')
                        ->label('Courier Partner')
                        ->placeholder('e.g. Blue Dart, Delhivery, DTDC, India Post')
                        ->default(fn (): ?string => $this->record->tracking_courier),

                    \Filament\Forms\Components\TextInput::make('tracking_number')
                        ->label('Tracking Number / AWB')
                        ->placeholder('e.g. BD987654321IN')
                        ->default(fn (): ?string => $this->record->tracking_number),

                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\DatePicker::make('estimated_delivery_at')
                                ->label('Estimated Delivery Date')
                                ->placeholder('Select expected date')
                                ->default(fn (): ?string => $this->record->estimated_delivery_at?->format('Y-m-d')),

                            \Filament\Forms\Components\Select::make('delivery_time_slot')
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
                                ->default(fn (): ?string => $this->record->delivery_time_slot),
                        ]),

                    \Filament\Forms\Components\Textarea::make('tracking_notes')
                        ->label('Delivery Update Note')
                        ->placeholder('e.g. Package arrived at local city hub, out for delivery today')
                        ->default(fn (): ?string => $this->record->tracking_notes)
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    $this->record->updateTrackingStatus(
                        newStatus: $data['fulfillment_status'],
                        courier: $data['tracking_courier'] ?? null,
                        trackingNumber: $data['tracking_number'] ?? null,
                        notes: $data['tracking_notes'] ?? null,
                        estimatedDeliveryAt: $data['estimated_delivery_at'] ?? null,
                        deliveryTimeSlot: $data['delivery_time_slot'] ?? null,
                    );

                    \Filament\Notifications\Notification::make()
                        ->title('Delivery Status Updated')
                        ->body(sprintf('Order %s is now marked as "%s".', $this->record->order_number, $this->record->getCasualStatusLabel()))
                        ->success()
                        ->send();

                    $this->refreshFormData(['fulfillment_status', 'tracking_courier', 'tracking_number', 'tracking_notes', 'estimated_delivery_at', 'delivery_time_slot', 'packed_at', 'shipped_at', 'delivered_at']);
                }),

            Actions\Action::make('advance_status')
                ->label('Next Step &rarr;')
                ->icon('heroicon-o-arrow-right')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Advance Delivery Step')
                ->modalDescription(fn (): string => sprintf(
                    'Advance order %s from "%s" to next step?',
                    $this->record->order_number,
                    $this->record->getCasualStatusLabel()
                ))
                ->visible(fn (): bool => $this->record->getCasualStatusKey() !== 'delivered')
                ->action(function (): void {
                    $this->record->advanceFulfillment();

                    \Filament\Notifications\Notification::make()
                        ->title('Delivery Advanced')
                        ->body(sprintf('Order %s is now "%s".', $this->record->order_number, $this->record->getCasualStatusLabel()))
                        ->success()
                        ->send();

                    $this->refreshFormData(['fulfillment_status', 'tracking_courier', 'tracking_number', 'tracking_notes', 'packed_at', 'shipped_at', 'delivered_at']);
                }),

            Actions\Action::make('cancel_consignment')
                ->label('Cancel Consignment')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->canBeCancelled())
                ->modalHeading(fn (): string => 'Cancel Consignment — ' . $this->record->order_number)
                ->modalDescription('Cancelling will release reserved vault inventory, issue a GST Credit Note, initiate online Razorpay refund, and notify patron.')
                ->form([
                    \Filament\Forms\Components\Select::make('reason')
                        ->label('Cancellation Reason')
                        ->required()
                        ->options(\App\Models\OrderCancellation::REASONS)
                        ->default('Changed my mind'),

                    \Filament\Forms\Components\Textarea::make('admin_notes')
                        ->label('Internal Concierge Notes')
                        ->placeholder('Optional staff notes...'),
                ])
                ->action(function (array $data): void {
                    $service = app(\App\Services\CancellationRefundService::class);
                    $cancellation = $service->requestCancellation(
                        order: $this->record,
                        reason: $data['reason'],
                        customerNotes: $data['admin_notes'] ?? null,
                        user: auth()->user(),
                    );

                    \Filament\Notifications\Notification::make()
                        ->title('Consignment Cancelled')
                        ->body(sprintf('Order %s cancelled under docket %s.', $this->record->order_number, $cancellation->cancellation_number))
                        ->success()
                        ->send();

                    $this->refreshFormData(['fulfillment_status', 'cancellation_status', 'refund_status', 'total_refunded']);
                }),

            Actions\Action::make('credit_note')
                ->label('View Credit Note')
                ->icon('heroicon-o-receipt-percent')
                ->color('gray')
                ->visible(fn (): bool => $this->record->creditNotes()->exists())
                ->url(fn (): string => route('credit_note.show', $this->record->creditNotes()->first()->credit_note_number))
                ->openUrlInNewTab(),

            Actions\Action::make('invoice')
                ->label('View Invoice')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->url(fn (): string => route('order.invoice', $this->record->order_number))
                ->openUrlInNewTab(),
        ];
    }
}
