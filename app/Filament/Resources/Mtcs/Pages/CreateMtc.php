<?php

namespace App\Filament\Resources\Mtcs\Pages;

use App\Filament\Resources\Mtcs\MtcResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CreateMtc extends CreateRecord
{
    protected static string $resource = MtcResource::class;

    public function getTitle(): string { return 'Add Non-Project'; }
    public function getBreadcrumb(): string { return 'Add Non-Project'; }

    protected function hasCreateAnother(): bool { return false; }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index'); // default setelah create
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $files = $data['attachments'] ?? [];
        $data['attachments_count'] = is_array($files) ? count($files) : 0;
        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            // SUBMIT -> simpan lalu ke List
            $this->getCreateFormAction()
                ->label('Submit')
                ->action(function () {
                    $this->create();
                    $this->redirect(static::getResource()::getUrl('index'));
                }),

            // DRAFT -> simpan, notif, ke Edit
            Action::make('draft')
                ->label('Draft')
                ->action(function () {
                    $this->create();

                    Notification::make()
                        ->title('Saved as draft')
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            $this->getCancelFormAction()->label('Cancel'),
        ];
    }
}
