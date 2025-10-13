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
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        $name = $this->getRecord()?->name;
        return $name ? ('Edit ' . $name) : 'Edit Non-Project Type';
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
