<?php

declare(strict_types=1);

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['approved_by'] = auth()->id();
        $data['approved_at'] = now();
        $data['is_approved'] = $data['is_approved'] ?? true;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
