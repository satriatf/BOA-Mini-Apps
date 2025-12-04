<?php

namespace App\Filament\Resources\MasterLeaveTypes\Pages;

use App\Filament\Resources\MasterLeaveTypes\MasterLeaveTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListMasterLeaveTypes extends ListRecords
{
    protected static string $resource = MasterLeaveTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Leave Type'),
        ];
    }

    public function getTitle(): string
    {
        return 'Leave Types';
    }

    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No Leave Types');
    }
}
