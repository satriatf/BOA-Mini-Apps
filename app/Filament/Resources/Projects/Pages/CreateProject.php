<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    public function getTitle(): string
    {
        return 'Add Project';
    }

    public function getBreadcrumb(): string
    {
    return 'Add Project';
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
