<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): TextInput
    {
        return TextInput::make('employee_nik')
            ->label('Employee NIK')
            ->required()
            ->autocomplete('off')
            ->autofocus()
            ->numeric()
            ->inputMode('numeric')
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'employee_nik' => $data['employee_nik'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.employee_nik' => 'The Employee NIK or Password you entered is incorrect. Please try again.',
        ]);
    }
}
