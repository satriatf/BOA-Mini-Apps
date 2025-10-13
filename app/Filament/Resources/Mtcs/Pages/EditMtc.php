<?php

namespace App\Filament\Resources\Mtcs\Pages;

use App\Filament\Resources\Mtcs\MtcResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditMtc extends EditRecord
{
    protected static string $resource = MtcResource::class;

    protected function getHeaderActions(): array
    {
        return [ DeleteAction::make()->label('Delete') ];
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

    public function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }

    /* ====== Judul & breadcrumb ====== */
    public function getHeading(): string|Htmlable
    {
        $app = $this->getRecord()?->application;
        return $app ? ('Edit ' . $app) : 'Edit Non-Project';
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getHeading();
    }

    public function getBreadcrumb(): string
    {
        $description = $this->getRecord()?->description;
        return $description ?: 'Edit';
    }
}
