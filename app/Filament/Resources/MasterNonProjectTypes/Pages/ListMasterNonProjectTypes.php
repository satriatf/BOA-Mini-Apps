<?php

namespace App\Filament\Resources\MasterNonProjectTypes\Pages;

use App\Filament\Resources\MasterNonProjectTypes\MasterNonProjectTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListMasterNonProjectTypes extends ListRecords
{
    protected static string $resource = MasterNonProjectTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Non-Project Type'),
        ];
    }

    public function getTitle(): string
    {
        return 'Non-Project Types';
    }

    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No non-project types yet');
    }
}
