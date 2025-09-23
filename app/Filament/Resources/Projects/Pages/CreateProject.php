<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    public function getTitle(): string { return 'Add Project'; }
    public function getBreadcrumb(): string { return 'Add Project'; }

    protected function hasCreateAnother(): bool { return false; }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /** Ensure pics data is properly formatted */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure pics is an array of user IDs
        if (isset($data['pics'])) {
            $data['pics'] = is_array($data['pics']) ? $data['pics'] : [];
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            // Submit → simpan → list
            $this->getCreateFormAction()
                ->label('Submit')
                ->action(function () {
                    $this->create();
                    $this->redirect(static::getResource()::getUrl('index'));
                }),

            // Draft → simpan → notif → edit
            Action::make('draft')
                ->label('Draft')
                ->action(function () {
                    $this->create();

                    Notification::make()
                        ->title('Saved as draftt')
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
