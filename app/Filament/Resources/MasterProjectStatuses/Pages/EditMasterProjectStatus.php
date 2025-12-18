<?php

namespace App\Filament\Resources\MasterProjectStatuses\Pages;

use App\Filament\Resources\MasterProjectStatuses\MasterProjectStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMasterProjectStatus extends EditRecord
{
    protected static string $resource = MasterProjectStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Delete')
                ->modalHeading(function ($record) {
                    $name = $record->name ?? 'this project status';
                    return 'DELETE "' . $name . '"';
                })
                ->modalDescription('Are you sure you would like to do this?'),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Project Status';
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
