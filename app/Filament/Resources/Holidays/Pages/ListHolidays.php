<?php

namespace App\Filament\Resources\Holidays\Pages;

use App\Filament\Resources\Holidays\HolidayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Holiday'),
        ];
    }

    public function getTitle(): string
    {
        return 'Holidays';
    }

    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No holidays yet');
    }
}
