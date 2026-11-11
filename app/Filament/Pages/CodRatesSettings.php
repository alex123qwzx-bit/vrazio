<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\SiteThemeSetting;
use App\Services\CartService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CodRatesSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Storefront Design';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Cash on Delivery State Rates';

    protected static string $view = 'filament.pages.cod-rates-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $rates = SiteThemeSetting::getSetting('cod_state_rates', []);
        if (! is_array($rates)) {
            $rates = json_decode((string) $rates, true) ?: [];
        }

        $defaultFee = $rates['default'] ?? 0;
        $formData = [
            'default_cod_fee' => $defaultFee,
        ];

        foreach (CartService::INDIAN_STATES as $state) {
            $key = 'state_' . md5($state);
            $formData[$key] = $rates[$state] ?? $defaultFee;
        }

        $this->form->fill($formData);
    }

    public function form(Form $form): Form
    {
        $stateFields = [];
        foreach (CartService::INDIAN_STATES as $state) {
            $key = 'state_' . md5($state);
            $stateFields[] = Forms\Components\TextInput::make($key)
                ->label($state)
                ->numeric()
                ->prefix('₹')
                ->default(0)
                ->required();
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Global Cash on Delivery (COD) Surcharge')
                    ->description('Default fee charged when patron selects Cash on Delivery for any unconfigured territory.')
                    ->schema([
                        Forms\Components\TextInput::make('default_cod_fee')
                            ->label('Default COD Fee (₹)')
                            ->numeric()
                            ->prefix('₹')
                            ->default(0)
                            ->required(),
                    ]),

                Forms\Components\Section::make('State & Union Territory COD Rates')
                    ->description('Set custom Cash on Delivery charges (in ₹) for each of the 36 Indian states & union territories. Enter 0 for Free COD.')
                    ->columns(3)
                    ->schema($stateFields),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $userId = Auth::id();

        $defaultFee = (float) ($state['default_cod_fee'] ?? 0);
        $rates = [
            'default' => $defaultFee,
        ];

        foreach (CartService::INDIAN_STATES as $indianState) {
            $key = 'state_' . md5($indianState);
            $rates[$indianState] = isset($state[$key]) ? (float) $state[$key] : $defaultFee;
        }

        SiteThemeSetting::setSetting('cod_state_rates', $rates, $userId);
        Cache::forget('cod_state_rates');

        Notification::make()
            ->title('COD State Rates Saved')
            ->body('Cash on Delivery rates for all 36 Indian states and union territories have been updated successfully.')
            ->success()
            ->send();
    }
}
