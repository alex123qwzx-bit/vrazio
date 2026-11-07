<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(5)
            )
            ->heading('Recent Atelier Orders')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Reference')
                    ->weight('bold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('client_name')
                    ->label('Patron')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total (INR)')
                    ->money('INR'),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ]),
                Tables\Columns\BadgeColumn::make('fulfillment_status')
                    ->colors([
                        'secondary' => 'inscribed',
                        'warning' => 'hallmarking',
                        'info' => 'armoured_transit',
                        'success' => 'delivered',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ordered')
                    ->since(),
            ])
            ->paginated(false);
    }
}
