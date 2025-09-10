<?php

namespace App\Filament\Resources\Mtcs\Pages;

use App\Filament\Resources\Mtcs\MtcResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateMtc extends CreateRecord
{
    protected static string $resource = MtcResource::class;

    public function getTitle(): string
    {
        return 'Add Non-Projects';
    }

    public function getBreadcrumb(): string
    {
    return 'Add Non-Projects';
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Create') // ubah "Create" jadi "Simpan"
            ->submit('create');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label('Save and Create Another')
            ->submit('createAnother');
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Cancel')
            ->url($this->getResource()::getUrl('index'));
    }
}
