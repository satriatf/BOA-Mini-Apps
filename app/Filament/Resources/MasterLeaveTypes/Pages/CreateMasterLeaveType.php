<?php

namespace App\Filament\Resources\MasterLeaveTypes\Pages;

use App\Filament\Resources\MasterLeaveTypes\MasterLeaveTypeResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateMasterLeaveType extends CreateRecord
{
    protected static string $resource = MasterLeaveTypeResource::class;

    public function getTitle(): string
    {
        return 'Create Leave Type';
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
