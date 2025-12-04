<?php

namespace App\Filament\Resources\OnLeaves\Pages;

use App\Filament\Resources\OnLeaves\OnLeaveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListOnLeaves extends ListRecords
{
    protected static string $resource = OnLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New On Leave'),
        ];
    }

    public function getTitle(): string
    {
        return 'On Leaves';
    }

    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No On Leaves');
    }
}
