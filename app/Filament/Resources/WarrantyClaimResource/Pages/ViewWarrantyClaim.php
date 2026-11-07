<?php

declare(strict_types=1);

namespace App\Filament\Resources\WarrantyClaimResource\Pages;

use App\Filament\Resources\WarrantyClaimResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewWarrantyClaim extends ViewRecord
{
    protected static string $resource = WarrantyClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_checked')
                ->label('Mark as Checked')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (): bool => ! $this->getRecord()->is_checked)
                ->action(function (): void {
                    $this->getRecord()->markAsChecked(auth()->id());

                    Notification::make()
                        ->title('Claim Checked')
                        ->body("Claim {$this->getRecord()->claim_number} is verified by concierge.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['is_checked', 'checked_by', 'checked_at']);
                }),

            Actions\EditAction::make()
                ->label('Update Status / Note'),
        ];
    }
}
