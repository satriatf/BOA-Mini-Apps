<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Delete Project'),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Cancel');
    }

    /** Map 'pics' -> pic_1/pic_2 sebelum update */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $pics = $this->form->getState()['pics'] ?? [];
        $pics = array_values(array_unique(array_map('intval', (array) $pics)));

        $data['pic_1'] = $pics[0] ?? null;
        $data['pic_2'] = implode(',', array_slice($pics, 1));

        return $data;
    }

    public function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }
    // ...existing code...
}
