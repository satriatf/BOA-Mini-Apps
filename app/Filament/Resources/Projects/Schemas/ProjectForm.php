<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pmo_id')
                    ->required(),
                TextInput::make('phase_cr'),
                TextInput::make('project_name')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                TextInput::make('tech_lead'),
                TextInput::make('pic_1'),
                TextInput::make('pic_2'),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                TextInput::make('days')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('percent_done')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
