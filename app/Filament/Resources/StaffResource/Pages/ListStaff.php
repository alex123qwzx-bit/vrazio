<?php

declare(strict_types=1);

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaff extends ListRecords
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Authentic Employee')
                ->visible(fn () => auth()->user()?->isSuperAdmin() ?? false),
        ];
    }
}
