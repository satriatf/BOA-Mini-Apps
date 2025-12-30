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
use Carbon\Carbon;

class ProjectForm
{
    /**
     * Calculate end date by adding working days (excluding weekends)
     */
    protected static function calculateEndDateExcludingWeekends(string $startDate, int $totalDays): string
    {
        $date = Carbon::parse($startDate);
        $addedDays = 0;

        // If total_days is 1, end date should be the same as start date if it's a weekday
        if ($totalDays === 1) {
            // If start date is a weekend, find the next Monday
            if ($date->isWeekend()) {
                while ($date->isWeekend()) {
                    $date->addDay();
                }
            }
            return $date->format('Y-m-d');
        }

        // For total_days > 1, we add (totalDays - 1) working days to the start date
        $daysToAdd = $totalDays - 1;
        
        while ($daysToAdd > 0) {
            $date->addDay();
            // Only count weekdays
            if (!$date->isWeekend()) {
                $daysToAdd--;
            }
        }

        return $date->format('Y-m-d');
    }
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
                ->disabledDates(function () {
                    // Block all Saturdays and Sundays
                    $disabledDates = [];
                    $startDate = Carbon::now()->subYear();
                    $endDate = Carbon::now()->addYears(5);
                    
                    while ($startDate <= $endDate) {
                        if ($startDate->isWeekend()) {
                            $disabledDates[] = $startDate->format('Y-m-d');
                        }
                        $startDate->addDay();
                    }
                    
                    return $disabledDates;
                })
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    // Calculate end_date when start_date changes
                    $totalDays = $get('total_day');
                    if ($state && $totalDays && $totalDays > 0) {
                        $endDate = self::calculateEndDateExcludingWeekends($state, $totalDays);
                        $set('end_date', $endDate);
                    }
                }),

            DatePicker::make('end_date')
                ->label('End Date')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->minDate(fn ($get) => $get('start_date'))
                ->closeOnDateSelection()
                ->disabled()
                ->dehydrated()
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
                ->live()
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    // Calculate end_date when total_day changes
                    $startDate = $get('start_date');
                    if ($startDate && $state && $state > 0) {
                        $endDate = self::calculateEndDateExcludingWeekends($startDate, $state);
                        $set('end_date', $endDate);
                    }
                })
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
