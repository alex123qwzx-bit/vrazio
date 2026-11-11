<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StaffResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Staff & Delegation';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'staff';

    /**
     * Only administrators and super administrators can see this navigation item.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /**
     * Scope query to only staff users (super_admin, admin, sub_manager).
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('role', ['super_admin', 'admin', 'sub_manager']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Staff Credentials & Role Authorization')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Employee full name'),

                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('employee@vrazio.com'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(15)
                                    ->placeholder('e.g. 9876543210'),

                                Forms\Components\Select::make('role')
                                    ->label('System Authority Role')
                                    ->options([
                                        'sub_manager' => 'Sub-Manager (Restricted Admin Access)',
                                        'admin' => 'Administrator (Full Operational Access)',
                                        'super_admin' => 'Super Administrator (Executive Master Control)',
                                    ])
                                    ->default('sub_manager')
                                    ->required()
                                    ->disabled(fn () => !auth()->user()?->isSuperAdmin())
                                    ->helperText('Sub-Managers can only access features once approved by Super Administrator.'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->rule(Password::min(8)->mixedCase()->numbers())
                                    ->helperText('Must be at least 8 characters with upper, lower, and numbers.'),

                                Forms\Components\Toggle::make('is_approved')
                                    ->label('Approved for Panel Access')
                                    ->default(fn (Forms\Get $get) => $get('role') !== 'sub_manager')
                                    ->disabled(fn () => !auth()->user()?->isSuperAdmin())
                                    ->helperText('Super Administrator authorization required for active panel access.'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Staff Member')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Administrator',
                        'sub_manager' => 'Sub-Manager',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'warning',
                        'admin' => 'success',
                        'sub_manager' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('is_approved')
                    ->label('Approval Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Approved' : 'Pending Approval')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved On')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('Pending Authorization')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin' => 'Administrator',
                        'sub_manager' => 'Sub-Manager',
                    ]),
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Approval')
                    ->trueLabel('Approved Only')
                    ->falseLabel('Pending Approval Only'),
            ])
            ->actions([
                // 1. Approve Sub-Manager Action (Super Admin Only)
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (User $record): bool => 
                        (auth()->user()?->isSuperAdmin() ?? false) && 
                        !$record->is_approved
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Approve Staff Account')
                    ->modalDescription(fn (User $record) => "Grant {$record->name} ({$record->email}) authorized access to the VRAZIO administration panel?")
                    ->action(function (User $record): void {
                        $record->update([
                            'is_approved' => true,
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Employee Approved')
                            ->body("{$record->name} has been approved and granted panel access.")
                            ->success()
                            ->send();
                    }),

                // 2. Revoke Access Action (Super Admin Only)
                Tables\Actions\Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => 
                        (auth()->user()?->isSuperAdmin() ?? false) && 
                        $record->is_approved && 
                        $record->id !== auth()->id()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Revoke Panel Access')
                    ->modalDescription(fn (User $record) => "Suspend {$record->name}'s administrative panel privileges?")
                    ->action(function (User $record): void {
                        $record->update([
                            'is_approved' => false,
                        ]);

                        Notification::make()
                            ->title('Access Revoked')
                            ->body("{$record->name}'s panel access has been suspended.")
                            ->warning()
                            ->send();
                    }),

                // 3. Delegate / Change Administration Authority (Super Admin Only)
                Tables\Actions\Action::make('delegate_authority')
                    ->label('Delegate')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (User $record): bool => 
                        (auth()->user()?->isSuperAdmin() ?? false) && 
                        $record->id !== auth()->id()
                    )
                    ->form([
                        Forms\Components\Select::make('new_role')
                            ->label('Assign Authority Level')
                            ->options([
                                'sub_manager' => 'Sub-Manager (Restricted Admin Access)',
                                'admin' => 'Administrator (Full Operational Access)',
                                'super_admin' => 'Super Administrator (Full Master Control & Delegation)',
                            ])
                            ->required()
                            ->default(fn (User $record) => $record->role),
                        Forms\Components\TextInput::make('superadmin_password')
                            ->label('Your Super Admin Password')
                            ->password()
                            ->required()
                            ->helperText('Confirm your super administrator password to authorize this delegation.'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $currentUser = auth()->user();
                        if (!Hash::check($data['superadmin_password'], $currentUser->password)) {
                            Notification::make()
                                ->title('Authorization Failed')
                                ->body('Incorrect Super Administrator password. Delegation aborted.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update([
                            'role' => $data['new_role'],
                            'is_approved' => true,
                            'approved_at' => now(),
                            'approved_by' => $currentUser->id,
                        ]);

                        Notification::make()
                            ->title('Administration Delegated')
                            ->body("{$record->name} is now designated as " . strtoupper($data['new_role']) . '.')
                            ->success()
                            ->send();
                    }),

                // 4. Change Password with Email Confirmation (Super Admin Feature)
                Tables\Actions\Action::make('change_password_secure')
                    ->label('Reset Password')
                    ->icon('heroicon-o-lock-closed')
                    ->color('gray')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->form([
                        Forms\Components\TextInput::make('new_password')
                            ->label('New Strong Password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule(Password::min(8)->mixedCase()->numbers())
                            ->helperText('Min 8 characters with upper, lower, numbers.'),
                        Forms\Components\TextInput::make('confirm_password')
                            ->label('Confirm New Password')
                            ->password()
                            ->required()
                            ->same('new_password'),
                        Forms\Components\TextInput::make('superadmin_password')
                            ->label('Your Super Admin Password')
                            ->password()
                            ->required()
                            ->helperText('Required to confirm security authorization.'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $currentUser = auth()->user();
                        if (!Hash::check($data['superadmin_password'], $currentUser->password)) {
                            Notification::make()
                                ->title('Authorization Failed')
                                ->body('Incorrect Super Administrator password.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Generate confirmation token and update password
                        $token = Str::random(40);
                        $record->update([
                            'password' => Hash::make($data['new_password']),
                            'password_confirmation_token' => $token,
                            'password_confirmation_expires_at' => now()->addHours(2),
                        ]);

                        // Send confirmation audit email to super admin and employee
                        try {
                            Mail::raw(
                                "Security Notice: The password for employee account {$record->email} ({$record->name}) was updated by Super Administrator {$currentUser->name} ({$currentUser->email}) on " . now()->toDateTimeString() . ".\n\nIf this was authorized, no further action is required.",
                                function ($message) use ($currentUser, $record) {
                                    $message->to($currentUser->email)
                                        ->cc($record->email)
                                        ->subject("[VRAZIO Atelier Security] Admin Password Updated for {$record->email}");
                                }
                            );
                        } catch (\Throwable $e) {
                            // Log and continue if mail delivery fails in dev
                            \Illuminate\Support\Facades\Log::warning("Password reset audit email failed: " . $e->getMessage());
                        }

                        Notification::make()
                            ->title('Password Successfully Updated')
                            ->body("Password for {$record->name} has been changed. A confirmation notice was dispatched to {$currentUser->email}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
