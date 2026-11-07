<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRevenue = (float) Order::where('payment_status', 'paid')->sum('total_amount');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('payment_status', 'pending')->count();
        $totalPatrons = User::where('role', 'patron')->count();

        return [
            Stat::make('Gross Revenue', '₹' . number_format($totalRevenue, 2, '.', ','))
                ->description('Total captured vault revenue')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([7000, 15000, 32000, 48000, 95000, 140000, $totalRevenue])
                ->color('success'),

            Stat::make('Total Commissions & Orders', (string) $totalOrders)
                ->description('All customer orders placed')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Pending Escrow', (string) $pendingOrders)
                ->description('Awaiting OTP / Razorpay fulfillment')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Registered Patrons', (string) $totalPatrons)
                ->description('Active clientele profiles')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
