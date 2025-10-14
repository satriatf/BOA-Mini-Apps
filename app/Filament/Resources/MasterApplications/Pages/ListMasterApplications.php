<?php

namespace App\Filament\Resources\MasterApplications\Pages;

use App\Filament\Resources\MasterApplications\MasterApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListMasterApplications extends ListRecords
{
    protected static string $resource = MasterApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Application'),
        ];
    }

    public function getTitle(): string
    {
        return 'Applications';
    }

    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No applications yet');
    }
}
