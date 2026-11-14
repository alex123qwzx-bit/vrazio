<?php

declare(strict_types=1);

namespace App\Filament\Resources\MembershipRequestResource\Pages;

use App\Filament\Resources\MembershipRequestResource;
use App\Filament\Resources\MembershipResource;
use App\Models\MembershipRequest;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMembershipRequests extends ListRecords
{
    protected static string $resource = MembershipRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_memberships')
                ->label('Active Memberships Registry &rarr;')
                ->icon('heroicon-o-identification')
                ->color('primary')
                ->url(MembershipResource::getUrl('index')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Requests'),

            'cancellations' => Tab::make('Cancellation Requests')
                ->badge(MembershipRequest::cancellations()->whereIn('status', [MembershipRequest::STATUS_PENDING_REVIEW, MembershipRequest::STATUS_UNDER_REVIEW])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->cancellations()),

            'refunds' => Tab::make('Refund Requests')
                ->badge(MembershipRequest::refunds()->whereIn('refund_status', [MembershipRequest::REFUND_PENDING_REVIEW, MembershipRequest::REFUND_UNDER_REVIEW, MembershipRequest::REFUND_PROCESSING])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->refunds()),

            'upgrades' => Tab::make('Upgrade Requests')
                ->badge(MembershipRequest::upgrades()->whereIn('status', [MembershipRequest::STATUS_PENDING_REVIEW, MembershipRequest::STATUS_UNDER_REVIEW, MembershipRequest::STATUS_PAYMENT_PENDING])->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->upgrades()),

            'active_memberships' => Tab::make('Active Memberships')
                ->badge(MembershipRequest::whereHas('membership', fn (Builder $q): Builder => $q->where('status', 'active'))->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereHas('membership', fn (Builder $q): Builder => $q->where('status', 'active'))),
        ];
    }
}
