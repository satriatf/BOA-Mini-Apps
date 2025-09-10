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
        return 'Tambah Project';
    }

    public function getBreadcrumb(): string
    {
    return 'Tambah Project';
    }

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Simpan') // ubah "Create" jadi "Simpan"
            ->submit('create');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return Action::make('createAnother')
            ->label('Simpan & Tambah Lagi')
            ->submit('createAnother');
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Batal')
            ->url($this->getResource()::getUrl('index'));
    }
}
