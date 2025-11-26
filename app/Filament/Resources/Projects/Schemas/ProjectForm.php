<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\View as FormsView;
use Filament\Schemas\Schema;
use App\Models\User;
use App\Models\MasterProjectStatus;
use Illuminate\Validation\Rules\Unique;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('project_ticket_no')
                ->label('Project Ticket No')
                ->required()
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')),

            TextInput::make('project_name')
                ->label('Project Name')
                ->required()
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')),

            Select::make('project_status')
                ->label('Project Status')
                ->required()
                ->options(fn() => MasterProjectStatus::pluck('name', 'name'))
                ->native(false)
                ->searchable(),

            Select::make('technical_lead')
                ->label('Technical Lead')
                ->options(fn() => User::where('is_active', 'Active')->where('level', 'Section Head')->pluck('employee_name', 'sk_user'))
                ->searchable()
                ->preload()
                ->native(false)
                ->required(),

            DatePicker::make('start_date')
                ->label('Start Date')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection()
                ->live()
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    // Reset end_date if it's less than start_date
                    if ($get('end_date') && $state && $get('end_date') < $state) {
                        $set('end_date', null);
                    }
                }),

            DatePicker::make('end_date')
                ->label('End Date')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->minDate(fn ($get) => $get('start_date'))
                ->closeOnDateSelection()
                ->live()
                ->disabled(fn ($get) => !$get('start_date'))
                ->rules(['after_or_equal:start_date']),

            TextInput::make('total_day')
                ->label('Total Days')
                ->type('text')
                ->inputMode('numeric')
                ->default(0)
                ->extraInputAttributes([
                    'pattern' => '[0-9]*',
                    'oninput' => "this.value=this.value.replace(/[^0-9]/g,'');",
                ])
                ->required(),

            TextInput::make('percent_done')
                ->label('% Done')
                ->type('text')
                ->inputMode('numeric')
                ->default(0)
                ->extraInputAttributes([
                    'pattern' => '[0-9]*',
                    'oninput' => "this.value=this.value.replace(/[^0-9]/g,'');",
                ])
                ->suffix('%')
                ->required(),
        ]);
    }
}
