<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\MasterProjectStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

            ])
            ->filters([
                SelectFilter::make('project_status')
                    ->label('Project Status')
                    ->options(fn () => MasterProjectStatus::pluck('name', 'name')->toArray())
                    ->searchable()
                    ->indicator('Status'),
                Filter::make('project_ticket_no')
                    ->label('Project Ticket No')
                    ->form([
                        TextInput::make('value')
                            ->label('Project Ticket No')
                            ->placeholder('e.g. PMO-123456'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $value) => $q->where('project_ticket_no', 'like', '%' . $value . '%'),
                        );
                    }),
                Filter::make('project_name')
                    ->label('Project Name')
                    ->form([
                        TextInput::make('value')
                            ->label('Project Name')
                            ->placeholder('e.g. Digitalisasi'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $value) => $q->where('project_name', 'like', '%' . $value . '%'),
                        );
                    }),
                Filter::make('tech_lead')
                    ->label('Technical Lead')
                    ->form([
                        TextInput::make('value')
                            ->label('Technical Lead')
                            ->placeholder('Nama PIC / Lead'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q, $value) => $q->whereHas(
                                'techLead',
                                fn (Builder $sub) => $sub->where('employee_name', 'like', '%' . $value . '%'),
                            ),
                        );
                    }),
                Filter::make('date_range')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('start_from')
                            ->label('Start Date From'),
                        DatePicker::make('end_to')
                            ->label('End Date To'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['start_from'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['end_to'] ?? null,
                                fn (Builder $q, $date) => $q->whereDate('end_date', '<=', $date),
                            );
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
