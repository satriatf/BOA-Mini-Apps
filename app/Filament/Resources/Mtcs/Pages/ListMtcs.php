<?php

namespace App\Filament\Resources\Mtcs\Pages;

use App\Filament\Resources\Mtcs\MtcResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMtcs extends ListRecords
{
    protected static string $resource = MtcResource::class;

    protected static ?string $title = 'MTC Tickets';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
