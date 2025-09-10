<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected static ?string $title = 'Create Employee';

    // Hilangkan "Create & create another"
    protected function hasCreateAnother(): bool
    {
        return false;
    }

    // Setelah Submit (create) → redirect ke List (tabel)
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            // Tombol Submit default (tetap pakai bawaan, hanya ganti label)
            $this->getCreateFormAction()->label('Submit'),

            Actions\Action::make('draft')
                ->label('Draft')
                ->action(function () {

                    $data = $this->form->getState();
                    $data['is_active'] = 'Inactive';
                    $this->form->fill($data);

                    $this->create();

                    Notification::make()
                        ->title('Saved as draft')
                        ->success()
                        ->send();

                    $this->redirect(
                        static::getResource()::getUrl('edit', ['record' => $this->record])
                    );
                }),

            $this->getCancelFormAction()->label('Cancel'),
        ];
    }
}
