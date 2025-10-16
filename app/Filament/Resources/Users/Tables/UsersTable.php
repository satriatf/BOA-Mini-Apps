<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('level', 'asc')
            ->searchPlaceholder('Search by name')
            ->columns([
                TextColumn::make('employee_nik')->label('Employee NIK'),
                TextColumn::make('employee_name')->label('Employee Name')->searchable(),
                TextColumn::make('employee_email')->label('Employee Email'),
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->sortable(query: function ($query, string $direction) {
                        // Custom sorting by hierarchy: Manager → Asmen → SH → Staff → Intern
                        return $query->orderByRaw("CASE 
                            WHEN level = 'Manager' THEN 1
                            WHEN level = 'Asmen' THEN 2
                            WHEN level = 'SH' THEN 3
                            WHEN level = 'Staff' THEN 4
                            WHEN level = 'Intern' THEN 5
                            ELSE 6
                        END {$direction}");
                    }),
                TextColumn::make('is_active')
                    ->label('Is Active')
                    ->badge()
                    ->color(fn ($state) => $state === 'Active' ? 'success' : 'danger'),
                TextColumn::make('join_date')->label('Join Date')->date(),
                TextColumn::make('end_date')->label('End Date')->date(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
