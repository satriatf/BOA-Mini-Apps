<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                        foreach ($pics as $i => $p) {
                            $name = optional($p->user)->employee_name ?? ($p->sk_user ?? '-');
                            $full = ($i + 1) . '. ' . $name;
                            $safe = e($full);
                            $safe = str_replace(' ', '&nbsp;', $safe);
                            $lines[] = $safe;
                        }

                        return implode('<br>', $lines);
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
            ->filters([])
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
