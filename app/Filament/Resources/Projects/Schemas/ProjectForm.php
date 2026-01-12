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
use App\Models\Holiday;
use Illuminate\Validation\Rules\Unique;
use Carbon\Carbon;

class ProjectForm
{
    /**
     * Calculate end date by adding working days (skip weekend & holidays)
     */
    protected static function calculateEndDateSkippingNonWorking(string $startDate, int $totalDays): string
    {
        $date = Carbon::parse($startDate);
        $holidays = Holiday::pluck('date')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();

        $isNonWorking = function (Carbon $d) use ($holidays) {
            return $d->isWeekend() || in_array($d->format('Y-m-d'), $holidays, true);
        };

        // If total_days is 1, end date = next working day (could be same day)
        if ($totalDays === 1) {
            while ($isNonWorking($date)) {
                $date->addDay();
            }
            return $date->format('Y-m-d');
        }

        // For total_days > 1, add (totalDays - 1) working days
        $daysToAdd = $totalDays - 1;
        while ($daysToAdd > 0) {
            $date->addDay();
            if (!$isNonWorking($date)) {
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
                        $endDate = self::calculateEndDateSkippingNonWorking($startDate, $state);
                        $set('end_date', $endDate);
                    }
                })
                ->required(),

            DatePicker::make('start_date')
                ->label('Start Date')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection()
                ->live()
                ->disabledDates(function () {
                    // Block all weekends and holidays
                    $disabledDates = [];
                    $startDate = Carbon::now()->subYear();
                    $endDate = Carbon::now()->addYears(5);
                    $holidayDates = Holiday::pluck('date')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();

                    while ($startDate <= $endDate) {
                        $formatted = $startDate->format('Y-m-d');
                        if ($startDate->isWeekend() || in_array($formatted, $holidayDates, true)) {
                            $disabledDates[] = $formatted;
                        }
                        $startDate->addDay();
                    }

                    return $disabledDates;
                })
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    // Calculate end_date when start_date changes
                    $totalDays = $get('total_day');
                    if ($state && $totalDays && $totalDays > 0) {
                        $endDate = self::calculateEndDateSkippingNonWorking($state, $totalDays);
                        $set('end_date', $endDate);
                    }
                }),

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

            DatePicker::make('end_date')
                ->label('End Date')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->minDate(fn ($get) => $get('start_date'))
                ->closeOnDateSelection()
                ->disabled()
                ->dehydrated()
                ->rules(['after_or_equal:start_date']),
        ]);
    }
}
