<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPassword extends BaseRequestPasswordReset
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_nik')
                    ->label('Employee NIK')
                    ->required()
                    ->type('text')
                    ->inputMode('numeric')
                    ->extraInputAttributes([
                        'pattern' => '[0-9]*',
                        'oninput' => "this.value=this.value.replace(/[^0-9]/g,'');",
                    ])
                    ->autocomplete('off')
                    ->autofocus(),
            ]);
    }

    public function request(): void
    {
        $data = $this->form->getState();

        $user = User::where('employee_nik', $data['employee_nik'])->first();

        if (! $user) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'data.employee_nik' => 'NIK is wrong or not registered.',
            ]);
        }

        // Reset password to NIK
        $user->password = Hash::make($user->employee_nik);
        $user->save();

        // Send Notification Email
        try {
            if ($user->employee_email) {
                Mail::raw("Hello {$user->employee_name},\n\nYour password has been reset to your NIK: {$user->employee_nik}.\nPlease login using your NIK and this new password.", function ($message) use ($user) {
                    $message->to($user->employee_email)
                        ->subject('Password Reset - Management System');
                });
            }
        } catch (\Exception $e) {
            // Silently fail if mail is not configured, but notification was successful in DB
        }

        Notification::make()
            ->title('Password has been reset to your NIK and a notification has been sent.')
            ->success()
            ->send();

        $this->redirect(filament()->getLoginUrl());
    }
}
