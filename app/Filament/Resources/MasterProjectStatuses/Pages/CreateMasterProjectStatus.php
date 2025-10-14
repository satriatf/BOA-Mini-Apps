<?php

namespace App\Filament\Resources\MasterProjectStatuses\Pages;

use App\Filament\Resources\MasterProjectStatuses\MasterProjectStatusResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CreateMasterProjectStatus extends CreateRecord
{
    protected static string $resource = MasterProjectStatusResource::class;

    public function getTitle(): string
    {
        return 'Create Project Status';
    }

    protected function hasCreateAnother(): bool
    {
        return false;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Submit')
                ->action(function () {
                    $this->create();
                    $this->redirect(static::getResource()::getUrl('index'));
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }
}
