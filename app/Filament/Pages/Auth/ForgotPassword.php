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
                'data.employee_nik' => 'Identity verification failed. Please try again.',
            ]);
        }

        // Reset password to NIK
        $user->password = Hash::make($user->employee_nik);
        $user->save();

        // Send Notification Email  
        try {
            if ($user->employee_email) {
                $mailContent = "Hello {$user->employee_name},\n\n"
                        . "Your password has been reset to your NIK: {$user->employee_nik}.\n"
                        . "Please login using your NIK and this new password.\n\n"
                        . "---\n"
                        . "This is an automated message from the MHC Management System. Please do not reply to this email.";

                Mail::raw($mailContent, function ($message) use ($user) {
                    $message->to($user->employee_email)
                        ->subject('Password Reset - MHC Management System');
                });
            }
        } catch (\Exception $e) {
            // Silently fail if mail is not configured
        }

        Notification::make()
            ->title('Password reset request processed. If the record is registered, a notification has been sent.')
            ->success()
            ->send();

        $this->redirect(filament()->getLoginUrl());
    }
}
