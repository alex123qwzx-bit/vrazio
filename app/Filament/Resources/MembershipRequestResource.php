<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MembershipRequestResource\Pages;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\MembershipRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class MembershipRequestResource extends Resource
{
    protected static ?string $model = MembershipRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'VRAZIO PRIVÉ CLUB';

    protected static ?string $navigationLabel = 'Membership Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'request_number';

    public static function getModelLabel(): string
    {
        return 'Membership Request';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Membership Requests';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = MembershipRequest::whereIn('status', [
            MembershipRequest::STATUS_PENDING_REVIEW,
            MembershipRequest::STATUS_UNDER_REVIEW,
            MembershipRequest::STATUS_PAYMENT_PENDING,
        ])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request Docket Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('request_number')
                        ->label('Docket #')
                        ->disabled(),

                    Forms\Components\Select::make('type')
                        ->label('Request Type')
                        ->options([
                            MembershipRequest::TYPE_CANCELLATION => 'Cancellation',
                            MembershipRequest::TYPE_REFUND => 'Refund',
                            MembershipRequest::TYPE_UPGRADE => 'Upgrade',
                        ])
                        ->disabled(),

                    Forms\Components\TextInput::make('user.name')
                        ->label('Customer Name')
                        ->disabled(),

                    Forms\Components\TextInput::make('user.email')
                        ->label('Customer Email')
                        ->disabled(),

                    Forms\Components\TextInput::make('user.phone')
                        ->label('Customer Phone')
                        ->disabled(),

                    Forms\Components\TextInput::make('membership.member_id')
                        ->label('Current Member ID')
                        ->disabled(),

                    Forms\Components\Select::make('status')
                        ->label('Current Status')
                        ->options([
                            MembershipRequest::STATUS_PENDING_REVIEW => 'Pending Review',
                            MembershipRequest::STATUS_UNDER_REVIEW => 'Under Review',
                            MembershipRequest::STATUS_APPROVED => 'Approved',
                            MembershipRequest::STATUS_REJECTED => 'Rejected',
                            MembershipRequest::STATUS_REFUND_PROCESSING => 'Refund Processing',
                            MembershipRequest::STATUS_REFUNDED => 'Refunded',
                            MembershipRequest::STATUS_PAYMENT_PENDING => 'Payment Pending',
                            MembershipRequest::STATUS_PAYMENT_RECEIVED => 'Payment Received',
                            MembershipRequest::STATUS_COMPLETED => 'Completed',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('reason')
                        ->label('Customer Reason')
                        ->disabled()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('customer_notes')
                        ->label('Customer Description')
                        ->disabled()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Internal Admin Notes')
                        ->placeholder('Internal staff notes...')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('admin_response')
                        ->label('Customer-Visible Response')
                        ->placeholder('Message visible to customer in their account tracking portal...')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('request_number')
                    ->label('Request ID')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Customer Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Customer Phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('membership.tier_name')
                    ->label('Current Plan')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cancellation' => 'danger',
                        'refund' => 'warning',
                        'upgrade' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Request Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(25)
                    ->tooltip(fn (MembershipRequest $record): string => $record->reason),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved', 'refunded', 'completed', 'payment_received' => 'success',
                        'rejected' => 'danger',
                        'pending_review', 'under_review', 'refund_processing', 'payment_pending' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'cancellation' => 'Cancellation',
                        'refund' => 'Refund',
                        'upgrade' => 'Upgrade',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_review' => 'Pending Review',
                        'under_review' => 'Under Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'refund_processing' => 'Refund Processing',
                        'refunded' => 'Refunded',
                        'payment_pending' => 'Payment Pending',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                // 1. CANCELLATION APPROVAL ACTION
                Tables\Actions\Action::make('approve_cancellation')
                    ->label('Approve Cancellation')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (MembershipRequest $record): bool => $record->type === MembershipRequest::TYPE_CANCELLATION
                        && in_array($record->status, [MembershipRequest::STATUS_PENDING_REVIEW, MembershipRequest::STATUS_UNDER_REVIEW], true)
                    )
                    ->form([
                        Forms\Components\Select::make('membership_status')
                            ->label('Update Membership Status')
                            ->options([
                                'cancelled' => 'Cancelled (Terminate Immediately)',
                                'active' => 'Active (Maintain access until paid expiry, disable renewal)',
                            ])
                            ->default('cancelled')
                            ->required(),

                        Forms\Components\Toggle::make('disable_auto_renewal')
                            ->label('Disable Auto-Renewal')
                            ->default(true),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes')
                            ->placeholder('Staff notes regarding approval...'),

                        Forms\Components\Textarea::make('admin_response')
                            ->label('Customer-Visible Response')
                            ->default('Your cancellation request has been approved by the atelier administration.')
                            ->required(),
                    ])
                    ->action(function (MembershipRequest $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $record->update([
                                'status' => MembershipRequest::STATUS_APPROVED,
                                'auto_renewal_disabled' => (bool) ($data['disable_auto_renewal'] ?? true),
                                'admin_notes' => $data['admin_notes'] ?? null,
                                'admin_response' => $data['admin_response'] ?? null,
                                'actioned_by' => auth()->id(),
                                'actioned_at' => now(),
                            ]);

                            $record->logAuditAction('approve_cancellation', auth()->id(), 'Cancellation approved by administrator.', MembershipRequest::STATUS_APPROVED);

                            // Update the membership record
                            $membership = $record->membership;
                            if ($membership) {
                                $membership->update([
                                    'status' => $data['membership_status'],
                                    'auto_renewal' => ! ($data['disable_auto_renewal'] ?? true),
                                    'cancelled_at' => now(),
                                    'cancellation_reason' => $record->reason,
                                ]);
                            }
                        });

                        Notification::make()
                            ->title("Cancellation Request #{$record->request_number} Approved")
                            ->success()
                            ->send();
                    }),

                // 2. CANCELLATION REJECTION ACTION
                Tables\Actions\Action::make('reject_cancellation')
                    ->label('Reject Cancellation')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (MembershipRequest $record): bool => $record->type === MembershipRequest::TYPE_CANCELLATION
                        && in_array($record->status, [MembershipRequest::STATUS_PENDING_REVIEW, MembershipRequest::STATUS_UNDER_REVIEW], true)
                    )
                    ->form([
                        Forms\Components\Textarea::make('admin_response')
                            ->label('Reason for Rejection (Customer-Visible)')
                            ->required(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes'),
                    ])
                    ->action(function (MembershipRequest $record, array $data): void {
                        $record->update([
                            'status' => MembershipRequest::STATUS_REJECTED,
                            'admin_response' => $data['admin_response'],
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'actioned_by' => auth()->id(),
                            'actioned_at' => now(),
                        ]);

                        $record->logAuditAction('reject_cancellation', auth()->id(), 'Cancellation rejected: '.$data['admin_response'], MembershipRequest::STATUS_REJECTED);

                        Notification::make()
                            ->title("Cancellation Request #{$record->request_number} Rejected")
                            ->danger()
                            ->send();
                    }),

                // 3. REFUND APPROVAL ACTION (Manual refund warning)
                Tables\Actions\Action::make('approve_refund')
                    ->label('Approve Refund')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn (MembershipRequest $record): bool => ($record->type === MembershipRequest::TYPE_REFUND || $record->is_refund_requested)
                        && in_array($record->refund_status, [MembershipRequest::REFUND_PENDING_REVIEW, MembershipRequest::REFUND_UNDER_REVIEW], true)
                    )
                    ->form([
                        Forms\Components\Placeholder::make('warning')
                            ->label('Notice')
                            ->content('IMPORTANT: Approving a refund does NOT automatically transfer money. The admin must manually execute the refund and mark it completed below once transferred.'),

                        Forms\Components\TextInput::make('refund_amount')
                            ->label('Approved Refund Amount (₹)')
                            ->numeric()
                            ->default(fn (MembershipRequest $record): float => (float) ($record->refund_amount > 0 ? $record->refund_amount : ($record->membership?->fee_paid ?? 0.00)))
                            ->required(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes'),

                        Forms\Components\Textarea::make('admin_response')
                            ->label('Customer-Visible Response')
                            ->default('Your refund request has been approved and is being processed by our accounts department.')
                            ->required(),
                    ])
                    ->action(function (MembershipRequest $record, array $data): void {
                        $record->update([
                            'refund_status' => MembershipRequest::REFUND_APPROVED,
                            'refund_amount' => (float) $data['refund_amount'],
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'admin_response' => $data['admin_response'] ?? null,
                            'actioned_by' => auth()->id(),
                            'actioned_at' => now(),
                        ]);

                        $record->logAuditAction('approve_refund', auth()->id(), 'Refund approved: ₹'.$data['refund_amount'], MembershipRequest::REFUND_APPROVED);

                        Notification::make()
                            ->title("Refund Request #{$record->request_number} Approved")
                            ->success()
                            ->send();
                    }),

                // 4. MARK REFUND AS PROCESSING
                Tables\Actions\Action::make('mark_refund_processing')
                    ->label('Mark Processing')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (MembershipRequest $record): bool => ($record->type === MembershipRequest::TYPE_REFUND || $record->is_refund_requested)
                        && $record->refund_status === MembershipRequest::REFUND_APPROVED
                    )
                    ->action(function (MembershipRequest $record): void {
                        $record->update([
                            'refund_status' => MembershipRequest::REFUND_PROCESSING,
                            'status' => MembershipRequest::STATUS_REFUND_PROCESSING,
                        ]);

                        $record->logAuditAction('refund_processing', auth()->id(), 'Refund marked as processing.', MembershipRequest::REFUND_PROCESSING);

                        Notification::make()
                            ->title("Refund #{$record->request_number} Marked as Processing")
                            ->info()
                            ->send();
                    }),

                // 5. MARK REFUND COMPLETED
                Tables\Actions\Action::make('mark_refund_completed')
                    ->label('Mark Refund Completed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (MembershipRequest $record): bool => ($record->type === MembershipRequest::TYPE_REFUND || $record->is_refund_requested)
                        && in_array($record->refund_status, [MembershipRequest::REFUND_APPROVED, MembershipRequest::REFUND_PROCESSING], true)
                    )
                    ->form([
                        Forms\Components\TextInput::make('refund_amount')
                            ->label('Final Refunded Amount (₹)')
                            ->numeric()
                            ->default(fn (MembershipRequest $record): float => (float) $record->refund_amount)
                            ->required(),

                        Forms\Components\TextInput::make('refund_transaction_id')
                            ->label('Transaction Reference / UTR ID')
                            ->placeholder('e.g. UTR12345678 or Razorpay rfn_...')
                            ->required(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes'),

                        Forms\Components\Textarea::make('admin_response')
                            ->label('Customer-Visible Message')
                            ->default('Your refund has been completed and credited to your original payment source.')
                            ->required(),
                    ])
                    ->action(function (MembershipRequest $record, array $data): void {
                        $record->update([
                            'refund_status' => MembershipRequest::REFUND_COMPLETED,
                            'status' => MembershipRequest::STATUS_REFUNDED,
                            'refund_amount' => (float) $data['refund_amount'],
                            'refund_transaction_id' => $data['refund_transaction_id'],
                            'refund_processed_at' => now(),
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'admin_response' => $data['admin_response'] ?? null,
                        ]);

                        $record->logAuditAction('refund_completed', auth()->id(), "Refund completed: ₹{$data['refund_amount']} (Ref: {$data['refund_transaction_id']})", MembershipRequest::REFUND_COMPLETED);

                        Notification::make()
                            ->title("Refund #{$record->request_number} Marked Completed")
                            ->success()
                            ->send();
                    }),

                // 6. REJECT REFUND
                Tables\Actions\Action::make('reject_refund')
                    ->label('Reject Refund')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (MembershipRequest $record): bool => ($record->type === MembershipRequest::TYPE_REFUND || $record->is_refund_requested)
                        && in_array($record->refund_status, [MembershipRequest::REFUND_PENDING_REVIEW, MembershipRequest::REFUND_UNDER_REVIEW], true)
                    )
                    ->form([
                        Forms\Components\Textarea::make('admin_response')
                            ->label('Reason for Refund Rejection (Customer-Visible)')
                            ->required(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes'),
                    ])
                    ->action(function (MembershipRequest $record, array $data): void {
                        $record->update([
                            'refund_status' => MembershipRequest::REFUND_REJECTED,
                            'admin_response' => $data['admin_response'],
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'actioned_by' => auth()->id(),
                            'actioned_at' => now(),
                        ]);

                        $record->logAuditAction('reject_refund', auth()->id(), 'Refund rejected: '.$data['admin_response'], MembershipRequest::REFUND_REJECTED);

                        Notification::make()
                            ->title("Refund Request #{$record->request_number} Rejected")
                            ->danger()
                            ->send();
                    }),

                // 7. UPGRADE APPROVAL ACTION
                Tables\Actions\Action::make('approve_upgrade')
                    ->label('Approve Upgrade')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->visible(fn (MembershipRequest $record): bool => $record->type === MembershipRequest::TYPE_UPGRADE
                        && in_array($record->status, [MembershipRequest::STATUS_PENDING_REVIEW, MembershipRequest::STATUS_UNDER_REVIEW], true)
                    )
                    ->form([
                        Forms\Components\Select::make('final_approved_plan_id')
                            ->label('Final Approved Plan')
                            ->options(fn (): array => MembershipPlan::active()->pluck('name', 'id')->toArray())
                            ->default(fn (MembershipRequest $record): ?int => $record->target_plan_id)
                            ->required(),

                        Forms\Components\TextInput::make('additional_payment_required')
                            ->label('Additional Payment Required (₹)')
                            ->numeric()
                            ->default(fn (MembershipRequest $record): float => (float) $record->additional_payment_required)
                            ->required(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes'),

                        Forms\Components\Textarea::make('admin_response')
                            ->label('Customer-Visible Message')
                            ->default('Your upgrade request has been approved. Please submit the differential payment to finalize elevation.')
                            ->required(),
                    ])
                    ->action(function (MembershipRequest $record, array $data): void {
                        $record->update([
                            'status' => MembershipRequest::STATUS_PAYMENT_PENDING,
                            'target_plan_id' => (int) $data['final_approved_plan_id'],
                            'additional_payment_required' => (float) $data['additional_payment_required'],
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'admin_response' => $data['admin_response'] ?? null,
                            'actioned_by' => auth()->id(),
                            'actioned_at' => now(),
                        ]);

                        $record->logAuditAction('approve_upgrade', auth()->id(), 'Upgrade approved. Payment pending: ₹'.$data['additional_payment_required'], MembershipRequest::STATUS_PAYMENT_PENDING);

                        Notification::make()
                            ->title("Upgrade Request #{$record->request_number} Approved")
                            ->success()
                            ->send();
                    }),

                // 8. UPGRADE CONFIRM PAYMENT & ACTIVATE ACTION
                Tables\Actions\Action::make('confirm_upgrade_payment')
                    ->label('Confirm Payment & Activate')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (MembershipRequest $record): bool => $record->type === MembershipRequest::TYPE_UPGRADE
                        && in_array($record->status, [MembershipRequest::STATUS_PAYMENT_PENDING, MembershipRequest::STATUS_APPROVED], true)
                    )
                    ->form([
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Payment Reference / UTR ID')
                            ->required(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes'),
                    ])
                    ->action(function (MembershipRequest $record, array $data): void {
                        DB::transaction(function () use ($record, $data): void {
                            $targetPlan = MembershipPlan::findOrFail((int) $record->target_plan_id);
                            $membership = $record->membership;

                            $record->update([
                                'status' => MembershipRequest::STATUS_COMPLETED,
                                'payment_reference' => $data['payment_reference'],
                                'payment_received_at' => now(),
                                'admin_notes' => $data['admin_notes'] ?? null,
                                'admin_response' => "Your membership has been elevated to {$targetPlan->name}! Welcome to enhanced atelier privileges.",
                            ]);

                            $record->logAuditAction('upgrade_activated', auth()->id(), "Upgrade activated to {$targetPlan->name} with payment ref: {$data['payment_reference']}", MembershipRequest::STATUS_COMPLETED);

                            // Activate new tier on existing membership
                            if ($membership) {
                                $membership->update([
                                    'membership_plan_id' => $targetPlan->id,
                                    'tier' => $targetPlan->slug,
                                    'tier_name' => $targetPlan->name,
                                    'fee_paid' => $targetPlan->price,
                                    'starts_at' => now(),
                                    'expires_at' => now()->addMonths((int) ($targetPlan->validity_months ?? 12)),
                                    'status' => 'active',
                                ]);
                            }
                        });

                        Notification::make()
                            ->title("Upgrade Activated for Membership #{$record->membership?->member_id}")
                            ->success()
                            ->send();
                    }),

                // 9. REJECT UPGRADE ACTION
                Tables\Actions\Action::make('reject_upgrade')
                    ->label('Reject Upgrade')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (MembershipRequest $record): bool => $record->type === MembershipRequest::TYPE_UPGRADE
                        && in_array($record->status, [MembershipRequest::STATUS_PENDING_REVIEW, MembershipRequest::STATUS_UNDER_REVIEW], true)
                    )
                    ->form([
                        Forms\Components\Textarea::make('admin_response')
                            ->label('Reason for Upgrade Rejection (Customer-Visible)')
                            ->required(),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes'),
                    ])
                    ->action(function (MembershipRequest $record, array $data): void {
                        $record->update([
                            'status' => MembershipRequest::STATUS_REJECTED,
                            'admin_response' => $data['admin_response'],
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'actioned_by' => auth()->id(),
                            'actioned_at' => now(),
                        ]);

                        $record->logAuditAction('reject_upgrade', auth()->id(), 'Upgrade rejected: '.$data['admin_response'], MembershipRequest::STATUS_REJECTED);

                        Notification::make()
                            ->title("Upgrade Request #{$record->request_number} Rejected")
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembershipRequests::route('/'),
        ];
    }
}
