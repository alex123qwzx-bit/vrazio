<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderCancellationResource\Pages;

use App\Filament\Resources\OrderCancellationResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderCancellations extends ListRecords
{
    protected static string $resource = OrderCancellationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
