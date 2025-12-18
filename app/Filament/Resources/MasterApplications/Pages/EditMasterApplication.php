<?php

namespace App\Filament\Resources\MasterApplications\Pages;

use App\Filament\Resources\MasterApplications\MasterApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMasterApplication extends EditRecord
{
    protected static string $resource = MasterApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Delete')
                ->modalHeading(function ($record) {
                    $name = $record->name ?? 'this application';
                    return 'DELETE "' . $name . '"';
                })
                ->modalDescription('Are you sure you would like to do this?'),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Application';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save Changes'),
            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }
}
