<?php

namespace App\Filament\Resources\Holidays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Holiday Date')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                TextInput::make('desc')
                    ->label('Description')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
