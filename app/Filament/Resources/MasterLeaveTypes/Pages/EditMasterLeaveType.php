<?php

namespace App\Filament\Resources\MasterLeaveTypes\Pages;

use App\Filament\Resources\MasterLeaveTypes\MasterLeaveTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMasterLeaveType extends EditRecord
{
    protected static string $resource = MasterLeaveTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Delete')
                ->modalHeading(function ($record) {
                    $name = $record->name ?? 'this leave type';
                    return 'DELETE "' . $name . '"';
                })
                ->modalDescription('Are you sure you would like to do this?'),
        ];
    }

    public function getTitle(): string
    {
        return 'Edit Leave Type';
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
