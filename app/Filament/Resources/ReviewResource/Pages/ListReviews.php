<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Models\ProductReview;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('+ Log Customer Review'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\ReviewResource\Widgets\ReviewOverviewWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Reviews')
                ->badge(ProductReview::count()),

            'pending' => Tab::make('Pending Moderation')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ProductReview::STATUS_PENDING))
                ->badge(ProductReview::where('status', ProductReview::STATUS_PENDING)->count())
                ->badgeColor('warning'),

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ProductReview::STATUS_APPROVED))
                ->badge(ProductReview::where('status', ProductReview::STATUS_APPROVED)->count())
                ->badgeColor('success'),

            'featured' => Tab::make('Homepage Featured')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->badge(ProductReview::where('is_featured', true)->count()),

            'verified' => Tab::make('Verified Purchases')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_verified_purchase', true))
                ->badge(ProductReview::where('is_verified_purchase', true)->count()),
        ];
    }
}
