<?php

namespace App\Filament\Resources\Holidays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Holiday Date')
                    ->required()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')
                    )
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                TextInput::make('desc')
                    ->label('Description')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
