<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminLogin extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.admin-login';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getSecurityPinFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    /**
     * Secondary protection layer: Optional Security PIN / Passcode.
     */
    protected function getSecurityPinFormComponent(): Component
    {
        return TextInput::make('security_pin')
            ->label('Security Passcode / PIN (Layer 2)')
            ->password()
            ->revealable()
            ->placeholder('Optional 4-6 digit concierge security PIN')
            ->maxLength(20)
            ->helperText('Required if secondary PIN protection is configured on your administrator profile.');
    }

    /**
     * Authenticate staff user with role check, approval verification, and 2nd layer PIN check.
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            $data = $this->form->getState();

            $user = User::where('email', strtolower(trim((string) $data['email'])))->first();

            if (! $user || ! Hash::check($data['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
                ]);
            }

            // Role verification: Must be Super Admin, Admin, or Sub-Manager
            if (! in_array($user->role, ['super_admin', 'admin', 'sub_manager'], true)) {
                throw ValidationException::withMessages([
                    'data.email' => 'Access Denied: This account does not possess staff or administrative authority.',
                ]);
            }

            // Approval verification: Sub-Managers and Admins must be approved
            if (! $user->isApproved()) {
                throw ValidationException::withMessages([
                    'data.email' => 'Access Restricted: Your staff account is awaiting formal approval from the Super Administrator.',
                ]);
            }

            // Secondary Protection Layer check: If user has an admin_pin set, verify it
            if (! empty($user->admin_pin)) {
                $providedPin = trim((string) ($data['security_pin'] ?? ''));
                if (empty($providedPin) || ! Hash::check($providedPin, $user->admin_pin)) {
                    throw ValidationException::withMessages([
                        'data.security_pin' => 'Secondary Security PIN invalid. Access denied.',
                    ]);
                }
            }

            Auth::login($user, $data['remember'] ?? false);
            session()->regenerate();

            return app(LoginResponse::class);
        } catch (ValidationException $e) {
            throw $e;
        }
    }
}
