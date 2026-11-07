<?php

declare(strict_types=1);

namespace App\Filament\Resources\WarrantyClaimResource\Pages;

use App\Filament\Resources\WarrantyClaimResource;
use Filament\Resources\Pages\ListRecords;

class ListWarrantyClaims extends ListRecords
{
    protected static string $resource = WarrantyClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
