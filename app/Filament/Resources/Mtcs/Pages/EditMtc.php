<?php

namespace App\Filament\Resources\Mtcs\Pages;

use App\Filament\Resources\Mtcs\MtcResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditMtc extends EditRecord
{
    protected static string $resource = MtcResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Delete'),
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Save Changes');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Cancel');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $files = $data['attachments'] ?? [];
        $data['attachments_count'] = is_array($files) ? count($files) : 0;
        return $data;
    }
}
