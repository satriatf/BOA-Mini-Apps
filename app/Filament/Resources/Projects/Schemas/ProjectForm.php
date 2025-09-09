<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
                Select::make('status')
                    ->required()
                    ->options([
                        'PEMBAHASAN' => 'PEMBAHASAN',
                        'TASKLIST' => 'TASKLIST',
                        'SIGN-OFF' => 'SIGN-OFF',
                        'NEED SCHEDULE' => 'NEED SCHEDULE',
                        'WTD' => 'WTD',
                        'DEV' => 'DEV',
                        'WTQ' => 'WTQ',
                        'QC' => 'QC',
                        'WAITING TO UAT' => 'WAITING TO UAT',
                        'UAT' => 'UAT',
                        'UAT DONE' => 'UAT DONE',
                        'READY TO DEPLOY' => 'READY TO DEPLOY',
                        'GO LIVE' => 'GO LIVE',
                        'PENDING PEMBAHASAN' => 'PENDING PEMBAHASAN',
                        'PENDING UAT' => 'PENDING UAT',
                        'NO IMPACT DEV' => 'NO IMPACT DEV',
                        'DROP' => 'DROP',
                        'PENDING DEV' => 'PENDING DEV',
                        'PENTEST' => 'PENTEST',
                    ]),
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
