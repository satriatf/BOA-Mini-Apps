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

    /** Map virtual field 'pics' -> pic_1 & pic_2 sebelum create */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $pics = $this->form->getState()['pics'] ?? [];
        $pics = array_values(array_unique(array_map('intval', (array) $pics)));

        // Simpan ke kolom lama
        $data['pic_1'] = $pics[0] ?? null;
        $data['pic_2'] = implode(',', array_slice($pics, 1));

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

                    Notification::make()->title('Saved as draft')->success()->send();

                    $this->redirect(
                        static::getResource()::getUrl('edit', ['record' => $this->record])
                    );
                }),

            $this->getCancelFormAction()->label('Cancel'),
        ];
    }
}
