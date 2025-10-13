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
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        $name = $this->getRecord()?->name;
        return $name ? ('Edit ' . $name) : 'Edit Project Status';
    }

    public function getHeading(): string
    {
        return $this->getTitle();
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

    public function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }
}
