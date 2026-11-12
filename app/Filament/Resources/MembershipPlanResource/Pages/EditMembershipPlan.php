<?php

declare(strict_types=1);

namespace App\Filament\Resources\MembershipPlanResource\Pages;

use App\Filament\Resources\MembershipPlanResource;
use Filament\Resources\Pages\EditRecord;

class EditMembershipPlan extends EditRecord
{
    protected static string $resource = MembershipPlanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
