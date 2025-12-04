<?php

namespace App\Filament\Resources\OnLeaves\Pages;

use App\Filament\Resources\OnLeaves\OnLeaveResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateOnLeave extends CreateRecord
{
    protected static string $resource = OnLeaveResource::class;

    public function getTitle(): string
    {
        return 'Create On Leave';
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
