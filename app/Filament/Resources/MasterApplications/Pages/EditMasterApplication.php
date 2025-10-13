<?php

namespace App\Filament\Resources\MasterApplications\Pages;

use App\Filament\Resources\MasterApplications\MasterApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMasterApplication extends EditRecord
{
    protected static string $resource = MasterApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        $name = $this->getRecord()?->name;
        return $name ? ('Edit ' . $name) : 'Edit Application';
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
