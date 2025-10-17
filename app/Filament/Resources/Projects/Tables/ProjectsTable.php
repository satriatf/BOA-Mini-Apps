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
                    ,

                TextColumn::make('project_name')
                    ->label('Project Name')
                    ->searchable(),

                TextColumn::make('project_status')
                    ->label('Project Status')
                    ,

                TextColumn::make('techLead.employee_name')
                    ->label('Technical Lead')
                    ->state(function ($record) {
                        return optional($record->techLead)->employee_name ?? '-';
                    })
                    ,

                TextColumn::make('pics')
                    ->label('PIC')
                    ->state(function ($record) {
                        if (empty($record->pics)) {
                            return '-';
                        }
                        
                        // Get user names based on IDs in pics array
                        $userIds = is_array($record->pics) ? $record->pics : [];
                        if (empty($userIds)) {
                            return '-';
                        }
                        
                        $users = \App\Models\User::whereIn('sk_user', $userIds)->get();
                        $names = $users->pluck('employee_name')->values()->all();
                        
                        if (empty($names)) {
                            return '-';
                        }

                        $lines = [];
                        foreach ($names as $i => $name) {
                            // Format: "1. Nama PIC"
                            $full = ($i + 1) . '. ' . $name;
                            $safe = e($full);
                            $safe = str_replace(' ', '&nbsp;', $safe);
                            $lines[] = $safe;
                        }

                        // Join with <br> for line breaks
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
