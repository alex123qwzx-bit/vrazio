<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.*')
                    ->selectSub(
                        OrderItem::selectRaw('COALESCE(SUM(quantity), 0)')
                            ->whereColumn('order_items.product_id', 'products.id'),
                        'total_ordered'
                    )
                    ->selectSub(
                        OrderItem::selectRaw('COALESCE(SUM(line_total), 0)')
                            ->whereColumn('order_items.product_id', 'products.id'),
                        'total_sales'
                    )
                    ->orderByDesc('total_ordered')
                    ->limit(5)
            )
            ->heading('High Jewellery Demand — Top Creations')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Piece Title')
                    ->weight('bold')
                    ->limit(35),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU'),
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'rings',
                        'warning' => 'necklaces',
                        'info' => 'bracelets',
                        'secondary' => 'earrings',
                        'danger' => 'solitaires',
                    ]),
                Tables\Columns\TextColumn::make('price')
                    ->label('List Price')
                    ->money('INR'),
                Tables\Columns\TextColumn::make('total_ordered')
                    ->label('Units Commissioned')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Gross Volume')
                    ->money('INR'),
            ])
            ->paginated(false);
    }
}
