<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderCancellationResource\Pages;

use App\Filament\Resources\OrderCancellationResource;
use App\Models\OrderCancellation;
use App\Services\CancellationRefundService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderCancellation extends ViewRecord
{
    protected static string $resource = OrderCancellationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve Request')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->getRecord()->status === OrderCancellation::STATUS_PENDING_REVIEW)
                ->requiresConfirmation()
                ->modalHeading('Approve Cancellation Request')
                ->modalDescription('Approving will update the order, restore inventory, issue an official GST Credit Note, initiate any Razorpay refund, and email the customer.')
                ->form([
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Internal Approval Notes')
                        ->placeholder('e.g., Verified item is in packing bay; returning to vault.'),
                ])
                ->action(function (array $data): void {
                    /** @var OrderCancellation $record */
                    $record = $this->getRecord();
                    $service = app(CancellationRefundService::class);
                    $service->processApprovedCancellation($record, auth()->user(), $data['admin_notes'] ?? null);

                    Notification::make()
                        ->title('Cancellation Approved')
                        ->body("Order #{$record->order?->order_number} cancellation docket {$record->cancellation_number} has been approved.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'admin_notes', 'is_stock_restored', 'stock_restored_at', 'approved_at', 'approved_by']);
                }),

            Actions\Action::make('reject')
                ->label('Reject Request')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->getRecord()->status === OrderCancellation::STATUS_PENDING_REVIEW)
                ->modalHeading('Reject Cancellation Request')
                ->modalDescription('Provide a clear operational reason for rejection. This will be recorded and emailed to the patron.')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->placeholder('e.g., Consignment has already passed armored transit security dispatch point.'),
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Internal Concierge Notes')
                        ->placeholder('Optional notes for staff records.'),
                ])
                ->action(function (array $data): void {
                    /** @var OrderCancellation $record */
                    $record = $this->getRecord();
                    $service = app(CancellationRefundService::class);
                    $service->rejectCancellation($record, $data['rejection_reason'], auth()->user(), $data['admin_notes'] ?? null);

                    Notification::make()
                        ->title('Cancellation Request Rejected')
                        ->body("Docket {$record->cancellation_number} was rejected and patron was informed.")
                        ->danger()
                        ->send();

                    $this->refreshFormData(['status', 'rejection_reason', 'admin_notes', 'rejected_at', 'rejected_by']);
                }),

            Actions\Action::make('execute_refund')
                ->label('Execute / Retry Refund')
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->visible(fn (): bool => $this->getRecord()->status === OrderCancellation::STATUS_APPROVED 
                    && (float) $this->getRecord()->refund_amount > 0 
                    && ! $this->getRecord()->refunds()->where('status', 'refunded')->exists())
                ->modalHeading('Execute Razorpay / Bank Refund')
                ->modalDescription('Initiate the refund via Razorpay server API (or record bank transfer for COD).')
                ->form([
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Refund Notes')
                        ->placeholder('e.g., Initiating refund through Razorpay.'),
                ])
                ->action(function (array $data): void {
                    /** @var OrderCancellation $record */
                    $record = $this->getRecord();
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
                            ->body("Refund #{$refund->refund_number} successfully initiated via {$refund->gateway}.")
                            ->success()
                            ->send();
                    }

                    $this->refreshFormData(['status']);
                }),

            Actions\Action::make('view_credit_note')
                ->label('View Credit Note')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->visible(fn (): bool => $this->getRecord()->order?->creditNotes?->isNotEmpty() ?? false)
                ->url(fn (): string => route('credit_note.show', $this->getRecord()->order->creditNotes->first()->credit_note_number))
                ->openUrlInNewTab(),
        ];
    }
}
