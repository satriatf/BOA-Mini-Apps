<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function hasRememberMe(): bool
    {
        return false;
    }

    protected function getRememberFormComponent(): \Filament\Schemas\Components\Component
    {
        return \Filament\Forms\Components\Hidden::make('remember');
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
            ]);
    }

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

    protected function getPasswordFormComponent(): \Filament\Schemas\Components\Component
    {
        return parent::getPasswordFormComponent()
            ->hint(null)
            ->helperText(new \Illuminate\Support\HtmlString(
                \Illuminate\Support\Facades\Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="3"> {{ __(\'filament-panels::auth/pages/login.actions.request_password_reset.label\') }}</x-filament::link>')
            ));
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
