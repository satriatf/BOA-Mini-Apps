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
            DeleteAction::make(),
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
