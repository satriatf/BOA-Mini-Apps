<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\MasterProjectStatus;
use App\Models\User;
use App\Models\Holiday;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search by project name')
            ->columns([
                TextColumn::make('project_ticket_no')
                    ->label('Project Ticket No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project_name')
                    ->label('Project Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project_status')
                    ->label('Project Status')
                    ->sortable(),

                TextColumn::make('application')
                    ->label('Application')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('techLead.employee_name')
                    ->label('Technical Lead')
                    ->state(function ($record) {
                        return optional($record->techLead)->employee_name ?? '-';
                    })
                    ->sortable(),

                TextColumn::make('pics')
                    ->label('PIC')
                    ->state(function ($record) {
                        $pics = $record->projectPics()->with('user')->get();
                        if ($pics->isEmpty()) {
                            return '-';
                        }
                        $lines = [];
                        $seen = [];
                        $index = 0;
                        foreach ($pics as $p) {
                            $uid = $p->sk_user ?? optional($p->user)->sk_user ?? null;
                            $name = optional($p->user)->employee_name ?? ($p->sk_user ?? '-');

                            if ($uid) {
                                if (isset($seen[$uid])) continue; // skip duplicate user entries
                                $seen[$uid] = true;
                            } else {
                                if (in_array($name, $seen, true)) continue;
                                $seen[] = $name;
                            }

                            $index++;
                            $full = $index . '. ' . $name;
                            $safe = e($full);
                            $safe = str_replace(' ', '&nbsp;', $safe);
                            $lines[] = $safe;
                        }
                        return empty($lines) ? '-' : implode('<br>', $lines);
                    })
                    ->html()
                    ->wrap(),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('total_day')
                    ->label('Total Days')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('percent_done')
                    ->label('% Done')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state . '%'),

                TextColumn::make('deploy_date')
                    ->label('Deploy Date')
                    ->date()
                    ->sortable(),

            ])
            ->filters([
                // Filter By List & Search By
                Filter::make('search_by')
                    ->label('Filter By')
                    ->form([
                        Select::make('field')
                            ->label('Select By')
                            ->options([
                                'project_ticket_no' => 'Project Ticket No',
                                'project_name' => 'Project Name',
                                'project_status' => 'Project Status',
                                'application' => 'Application',
                                'technical_lead' => 'Technical Lead',
                                'pics' => 'PIC',
                                'start_date' => 'Start Date',
                                'end_date' => 'End Date',
                                'total_day' => 'Total Day',
                                'percent_done' => 'Percent Done',
                                'deploy_date' => 'Deploy Date',
                            ])
                            ->placeholder('Select field to filter')
                            ->reactive(),
                        TextInput::make('search_value')
                            ->label('Search By')
                            ->placeholder(function ($get) {
                                $field = $get('field');
                                return match ($field) {
                                    'project_ticket_no' => 'Search by Project Ticket No...',
                                    'project_name' => 'Search by Project Name...',
                                    'total_day' => 'Search by Total Day...',
                                    'percent_done' => 'Search by Percent Done...',
                                    default => 'Type to search...',
                                };
                            })
                            ->visible(fn ($get) => in_array($get('field'), [
                                'project_ticket_no', 'project_name', 
                                'application', 'total_day', 'percent_done'
                            ])),
                        Select::make('status_value')
                            ->label('Select By')
                            ->options(fn () => MasterProjectStatus::pluck('name', 'name')->toArray())
                            ->searchable()
                            ->placeholder('Select by Project Status...')
                            ->visible(fn ($get) => $get('field') === 'project_status'),
                        Select::make('technical_lead_value')
                            ->label('Select By')
                            ->options(fn () => User::where('is_active', 'Active')
                                ->whereNotNull('employee_name')
                                ->whereIn('level', ['SH', 'Section Head'])
                                ->orderBy('employee_name')
                                ->pluck('employee_name', 'sk_user')
                                ->toArray())
                            ->searchable()
                            ->placeholder('Select by Technical Lead...')
                            ->visible(fn ($get) => $get('field') === 'technical_lead'),
                        Select::make('pics_value')
                            ->label('Select By')
                            ->options(fn () => User::where('is_active', 'Active')
                                ->whereNotNull('employee_name')
                                ->whereIn('level', ['STAFF', 'Staff'])
                                ->orderBy('employee_name')
                                ->pluck('employee_name', 'sk_user')
                                ->toArray())
                            ->searchable()
                            ->placeholder('Select by PIC...')
                            ->visible(fn ($get) => $get('field') === 'pics'),
                        DatePicker::make('start_date_value')
                            ->label('Select By')
                            ->native(false)
                            ->placeholder('Select by Start Date...')
                            ->disabledDates(function () {
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
                            ->visible(fn ($get) => $get('field') === 'start_date'),
                        DatePicker::make('end_date_value')
                            ->label('Select By')
                            ->native(false)
                            ->placeholder('Select by End Date...')
                            ->disabledDates(function () {
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
                            ->visible(fn ($get) => $get('field') === 'end_date'),
                        DatePicker::make('deploy_date_value')
                            ->label('Select By')
                            ->native(false)
                            ->placeholder('Select by Deploy Date...')
                            ->disabledDates(function () {
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
                            ->visible(fn ($get) => $get('field') === 'deploy_date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $field = $data['field'] ?? null;
                        $searchValue = $data['search_value'] ?? null;
                        $statusValue = $data['status_value'] ?? null;
                        $technicalLeadValue = $data['technical_lead_value'] ?? null;
                        $picsValue = $data['pics_value'] ?? null;
                        $startDateValue = $data['start_date_value'] ?? null;
                        $endDateValue = $data['end_date_value'] ?? null;
                        $deployDateValue = $data['deploy_date_value'] ?? null;

                        if (!$field) {
                            return $query;
                        }

                        return match ($field) {
                            'project_ticket_no' => $query->when(
                                $searchValue,
                                fn (Builder $q) => $q->whereRaw('LOWER(project_ticket_no) LIKE ?', ['%' . strtolower($searchValue) . '%'])
                            ),
                            'project_name' => $query->when(
                                $searchValue,
                                fn (Builder $q) => $q->whereRaw('LOWER(project_name) LIKE ?', ['%' . strtolower($searchValue) . '%'])
                            ),
                            'project_status' => $query->when(
                                $statusValue,
                                fn (Builder $q) => $q->where('project_status', $statusValue)
                            ),
                            'application' => $query->when(
                                $searchValue,
                                fn (Builder $q) => $q->whereRaw('LOWER(application) LIKE ?', ['%' . strtolower($searchValue) . '%'])
                            ),
                            'technical_lead' => $query->when(
                                $technicalLeadValue,
                                fn (Builder $q) => $q->where('technical_lead', (string) $technicalLeadValue)
                            ),
                            'pics' => $query->when(
                                $picsValue,
                                fn (Builder $q) => $q->whereHas('projectPics', function (Builder $sub) use ($picsValue) {
                                    $sub->where('sk_user', (string) $picsValue);
                                })
                            ),
                            'start_date' => $query->when(
                                $startDateValue,
                                fn (Builder $q) => $q->whereDate('start_date', $startDateValue)
                            ),
                            'end_date' => $query->when(
                                $endDateValue,
                                fn (Builder $q) => $q->whereDate('end_date', $endDateValue)
                            ),
                            'deploy_date' => $query->when(
                                $deployDateValue,
                                fn (Builder $q) => $q->whereDate('deploy_date', $deployDateValue)
                            ),
                            'total_day' => $query->when(
                                $searchValue !== null && $searchValue !== '',
                                fn (Builder $q) => $q->where('total_day', $searchValue)
                            ),
                            'percent_done' => $query->when(
                                $searchValue !== null && $searchValue !== '',
                                fn (Builder $q) => $q->where('percent_done', 'like', '%' . $searchValue . '%')
                            ),
                            default => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $field = $data['field'] ?? null;
                        $searchValue = $data['search_value'] ?? null;
                        $statusValue = $data['status_value'] ?? null;
                        $technicalLeadValue = $data['technical_lead_value'] ?? null;
                        $picsValue = $data['pics_value'] ?? null;
                        $startDateValue = $data['start_date_value'] ?? null;
                        $endDateValue = $data['end_date_value'] ?? null;
                        $deployDateValue = $data['deploy_date_value'] ?? null;

                        if (!$field) {
                            return null;
                        }

                        $fieldLabels = [
                            'project_ticket_no' => 'Project Ticket No',
                            'project_name' => 'Project Name',
                            'project_status' => 'Project Status',
                            'application' => 'Application',
                            'technical_lead' => 'Technical Lead',
                            'pics' => 'PIC',
                            'start_date' => 'Start Date',
                            'end_date' => 'End Date',
                            'total_day' => 'Total Day',
                            'percent_done' => 'Percent Done',
                            'deploy_date' => 'Deploy Date',
                        ];

                        if ($field === 'project_status') {
                            $value = $statusValue;
                        } elseif ($field === 'technical_lead') {
                            $value = $technicalLeadValue ? User::find($technicalLeadValue)?->employee_name : null;
                        } elseif ($field === 'pics') {
                            $value = $picsValue ? User::find($picsValue)?->employee_name : null;
                        } elseif ($field === 'start_date') {
                            $value = $startDateValue;
                        } elseif ($field === 'end_date') {
                            $value = $endDateValue;
                        } elseif ($field === 'deploy_date') {
                            $value = $deployDateValue;
                        } else {
                            $value = $searchValue;
                        }
                        
                        if ($value) {
                            return $fieldLabels[$field] . ': ' . $value;
                        }

                        return null;
                    }),
            ])
            ->filtersTriggerAction(fn ($action) => $action
                ->button()
                ->label('Filter')
                ->icon('heroicon-o-funnel'),
            )
            ->recordActions([
                EditAction::make()->label('Edit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Delete Selected'),
                ]),
            ]);
    }
}
