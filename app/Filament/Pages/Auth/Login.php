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
            ->type('text')
            ->inputMode('numeric')
            ->extraInputAttributes([
                'tabindex' => 1,
                'pattern' => '[0-9]*',
                'oninput' => "this.value=this.value.replace(/[^0-9]/g,'');",
            ]);
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
