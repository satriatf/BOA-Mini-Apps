<?php

namespace App\Filament\Resources\MasterApplications\Pages;

use App\Filament\Resources\MasterApplications\MasterApplicationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CreateMasterApplication extends CreateRecord
{
    protected static string $resource = MasterApplicationResource::class;

    public function getTitle(): string
    {
        return 'Create Application';
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
