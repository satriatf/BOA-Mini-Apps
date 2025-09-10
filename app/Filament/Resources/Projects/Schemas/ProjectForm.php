<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\User;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('pmo_id')
                ->label('PMO ID')
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('project_name')
                ->label('Project Name')
                ->required(),

            Select::make('status')
                ->label('Status')
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
                ])
                ->native(false)
                ->searchable(),

            Select::make('tech_lead')
                ->label('Tech Lead')
                ->options(fn () => User::where('level', 'SH')->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->native(false)
                ->required(),

            Select::make('pics')
                ->label('PIC')
                ->options(fn () => User::where('level', 'Staff')->pluck('name', 'id'))
                ->multiple()
                ->searchable()
                ->preload()
                ->native(false)         
                ->placeholder('Select PIC')
                ->helperText('Select one or more PICs (Staff level).'),

            DatePicker::make('start_date')
                ->label('Start Date')
                ->native(false)
                ->displayFormat('d/m/Y'),

            DatePicker::make('end_date')
                ->label('End Date')
                ->native(false)
                ->displayFormat('d/m/Y'),

            TextInput::make('days')
                ->label('Days')
                ->numeric()
                ->default(0)
                ->required(),

            TextInput::make('percent_done')
                ->label('% Done')
                ->numeric()
                ->default(0)
                ->suffix('%')
                ->required(),
        ]);
    }
}
