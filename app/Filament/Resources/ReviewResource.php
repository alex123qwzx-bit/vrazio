<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationGroup = 'Orders & Patrons';

    protected static ?string $navigationLabel = 'Customer Reviews';

    protected static ?string $modelLabel = 'Customer Review';

    protected static ?string $pluralModelLabel = 'Customer Reviews';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', ProductReview::STATUS_PENDING)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Left main column (span 2)
                        Forms\Components\Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Section::make('Review Details')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('product_id')
                                                    ->label('Jewellery / Couture Piece')
                                                    ->options(Product::pluck('title', 'id'))
                                                    ->searchable()
                                                    ->required(),
                                                Forms\Components\Select::make('rating')
                                                    ->label('Rating (1 to 5 Stars)')
                                                    ->options([
                                                        5 => '★★★★★ (5 Stars - Exceptional)',
                                                        4 => '★★★★☆ (4 Stars - Highly Commended)',
                                                        3 => '★★★☆☆ (3 Stars - Satisfactory)',
                                                        2 => '★★☆☆☆ (2 Stars - Below Standard)',
                                                        1 => '★☆☆☆☆ (1 Star - Poor)',
                                                    ])
                                                    ->required()
                                                    ->default(5),
                                            ]),

                                        Forms\Components\TextInput::make('title')
                                            ->label('Review Headline')
                                            ->required()
                                            ->maxLength(191),

                                        Forms\Components\Textarea::make('content')
                                            ->label('Patron Experience & Feedback')
                                            ->required()
                                            ->rows(5)
                                            ->columnSpanFull(),

                                        Forms\Components\FileUpload::make('photos')
                                            ->label('Customer Uploaded Photographs')
                                            ->disk('public')
                                            ->directory('reviews')
                                            ->multiple()
                                            ->image()
                                            ->reorderable()
                                            ->columnSpanFull(),
                                    ]),

                                Forms\Components\Section::make('Official Atelier Concierge Response')
                                    ->description('Respond publicly to this customer testimonial on the storefront.')
                                    ->schema([
                                        Forms\Components\Textarea::make('admin_reply')
                                            ->label('Concierge Response')
                                            ->rows(3)
                                            ->placeholder('Thank you for choosing VRAZIO Haute Joaillerie...'),

                                        Forms\Components\DateTimePicker::make('admin_replied_at')
                                            ->label('Replied At')
                                            ->disabled(),
                                    ]),
                            ]),

                        // Right column (span 1)
                        Forms\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Section::make('Moderation & Placement')
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Approval Status')
                                            ->options([
                                                ProductReview::STATUS_PENDING => 'Pending Moderation',
                                                ProductReview::STATUS_APPROVED => 'Approved (Visible Publicly)',
                                                ProductReview::STATUS_REJECTED => 'Rejected (Hidden)',
                                            ])
                                            ->default(ProductReview::STATUS_PENDING)
                                            ->required()
                                            ->live(),

                                        Forms\Components\Toggle::make('is_verified_purchase')
                                            ->label('Verified Purchase Badge')
                                            ->helperText('Indicates order was delivered and authenticated in records.')
                                            ->default(false),

                                        Forms\Components\Toggle::make('is_featured')
                                            ->label('Feature on Homepage')
                                            ->helperText('Spotlight in the Homepage Customer Reviews section.')
                                            ->default(false),

                                        Forms\Components\TextInput::make('sort_order')
                                            ->label('Homepage Display Order')
                                            ->numeric()
                                            ->default(0),
                                    ]),

                                Forms\Components\Section::make('Patron & Order Association')
                                    ->schema([
                                        Forms\Components\Select::make('user_id')
                                            ->label('Patron Account')
                                            ->options(User::pluck('name', 'id'))
                                            ->searchable()
                                            ->required(),

                                        Forms\Components\Select::make('order_id')
                                            ->label('Associated Order')
                                            ->options(Order::pluck('order_number', 'id'))
                                            ->searchable()
                                            ->nullable(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.images')
                    ->label('Product')
                    ->circular()
                    ->getStateUsing(fn (ProductReview $record) => $record->product?->images[0] ?? null),

                Tables\Columns\TextColumn::make('product.title')
                    ->label('Piece Title')
                    ->searchable()
                    ->sortable()
                    ->limit(24)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Patron')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ProductReview $record) => $record->user?->email),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->color('warning'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Review')
                    ->searchable()
                    ->limit(28)
                    ->description(fn (ProductReview $record) => \Illuminate\Support\Str::limit($record->content, 40)),

                Tables\Columns\IconColumn::make('is_verified_purchase')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ProductReview::STATUS_APPROVED => 'success',
                        ProductReview::STATUS_PENDING => 'warning',
                        ProductReview::STATUS_REJECTED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ProductReview::STATUS_PENDING => 'Pending Moderation',
                        ProductReview::STATUS_APPROVED => 'Approved',
                        ProductReview::STATUS_REJECTED => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        5 => '5 Stars (★★★★★)',
                        4 => '4 Stars (★★★★☆)',
                        3 => '3 Stars (★★★☆☆)',
                        2 => '2 Stars (★★☆☆☆)',
                        1 => '1 Star  (★☆☆☆☆)',
                    ]),

                Tables\Filters\TernaryFilter::make('is_verified_purchase')
                    ->label('Verified Purchase'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured on Homepage'),

                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Filter by Product')
                    ->options(Product::pluck('title', 'id'))
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ProductReview $record): bool => $record->status !== ProductReview::STATUS_APPROVED)
                    ->action(function (ProductReview $record) {
                        $record->update([
                            'status' => ProductReview::STATUS_APPROVED,
                            'approved_at' => now(),
                            'approved_by' => Auth::id(),
                        ]);
                        Notification::make()
                            ->title('Review Approved')
                            ->body('The review is now visible to customers on the storefront.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (ProductReview $record): bool => $record->status !== ProductReview::STATUS_REJECTED)
                    ->action(function (ProductReview $record) {
                        $record->update(['status' => ProductReview::STATUS_REJECTED]);
                        Notification::make()
                            ->title('Review Rejected')
                            ->body('The review has been hidden from public display.')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('info')
                    ->form([
                        Forms\Components\Textarea::make('admin_reply')
                            ->label('Concierge Response')
                            ->required()
                            ->rows(4)
                            ->default(fn (ProductReview $record) => $record->admin_reply),
                    ])
                    ->action(function (ProductReview $record, array $data) {
                        $record->update([
                            'admin_reply' => $data['admin_reply'],
                            'admin_replied_at' => now(),
                            'admin_reply_by' => Auth::id(),
                        ]);
                        Notification::make()
                            ->title('Response Saved')
                            ->body('Your concierge reply will display alongside the customer review.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_bulk')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn ($r) => $r->update([
                                'status' => ProductReview::STATUS_APPROVED,
                                'approved_at' => now(),
                                'approved_by' => Auth::id(),
                            ]));
                        }),

                    Tables\Actions\BulkAction::make('reject_bulk')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->each(fn ($r) => $r->update(['status' => ProductReview::STATUS_REJECTED]));
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\ReviewResource\Widgets\ReviewOverviewWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
