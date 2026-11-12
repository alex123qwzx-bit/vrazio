<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReviewResource\Widgets;

use App\Models\ProductReview;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReviewOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total = ProductReview::count();
        $pending = ProductReview::where('status', ProductReview::STATUS_PENDING)->count();
        $approved = ProductReview::where('status', ProductReview::STATUS_APPROVED)->count();
        $avg = ProductReview::where('status', ProductReview::STATUS_APPROVED)->avg('rating');
        $verified = ProductReview::where('is_verified_purchase', true)->count();

        return [
            Stat::make('Total Reviews', (string) $total)
                ->description('All customer submissions')
                ->icon('heroicon-o-chat-bubble-left-right'),

            Stat::make('Pending Moderation', (string) $pending)
                ->description($pending > 0 ? 'Action required by administrator' : 'All reviews reviewed')
                ->color($pending > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make('Approved Testimonials', (string) $approved)
                ->description("{$verified} verified buyer reviews")
                ->color('success')
                ->icon('heroicon-o-check-badge'),

            Stat::make('Storefront Average Rating', $avg ? number_format((float) $avg, 1) . ' / 5.0 ★' : '5.0 / 5.0 ★')
                ->description('Based on approved patron reviews')
                ->color('warning')
                ->icon('heroicon-o-star'),
        ];
    }
}
