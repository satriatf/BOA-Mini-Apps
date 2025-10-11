<?php

namespace App\Filament\Resources\MasterProjectStatuses\Pages;

use App\Filament\Resources\MasterProjectStatuses\MasterProjectStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListMasterProjectStatuses extends ListRecords
{
    protected static string $resource = MasterProjectStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Project Status'),
        ];
    }

    public function getTitle(): string
    {
        return 'Project Statuses';
    }

    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No project statuses yet');
    }
}
