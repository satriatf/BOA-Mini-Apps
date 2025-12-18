<?php

namespace App\Filament\Resources\MasterNonProjectTypes\Pages;

use App\Filament\Resources\MasterNonProjectTypes\MasterNonProjectTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMasterNonProjectType extends EditRecord
{
    protected static string $resource = MasterNonProjectTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Delete')
                ->modalHeading(function ($record) {
                    $name = $record->name ?? 'this non-project type';
                    return 'DELETE "' . $name . '"';
                })
                ->modalDescription('Are you sure you would like to do this?'),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Non-Project Type';
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
