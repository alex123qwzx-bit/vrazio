<?php

declare(strict_types=1);

namespace App\Filament\Resources\WarrantyClaimResource\Pages;

use App\Filament\Resources\WarrantyClaimResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWarrantyClaim extends EditRecord
{
    protected static string $resource = WarrantyClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['is_checked']) && empty($this->getRecord()->checked_at)) {
            $data['checked_by'] = auth()->id();
            $data['checked_at'] = now();
        }

        return $data;
    }
}
