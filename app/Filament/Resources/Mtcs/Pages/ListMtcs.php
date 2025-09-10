<?php

namespace App\Filament\Resources\Mtcs\Pages;

use App\Filament\Resources\Mtcs\MtcResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListMtcs extends ListRecords
{
    protected static string $resource = MtcResource::class;

    protected static ?string $title = 'Non-Project';

   protected function getHeaderActions(): array
    {
        return [
            CreateAction::make() ->label('Add Non-Project'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No Projects Has Been Added :(');
    }
}
