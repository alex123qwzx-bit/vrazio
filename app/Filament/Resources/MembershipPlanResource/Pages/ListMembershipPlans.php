<?php

declare(strict_types=1);

namespace App\Filament\Resources\MembershipPlanResource\Pages;

use App\Filament\Resources\MembershipPlanResource;
use Filament\Resources\Pages\ListRecords;

class ListMembershipPlans extends ListRecords
{
    protected static string $resource = MembershipPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
