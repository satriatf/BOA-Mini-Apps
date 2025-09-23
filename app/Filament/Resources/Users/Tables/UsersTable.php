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
            ->columns([
                TextColumn::make('employee_nik')->label('Employee NIK')->searchable(),
                TextColumn::make('employee_name')->label('Employee Name')->searchable(),
                TextColumn::make('employee_email')->label('Employee Email')->searchable(),
                TextColumn::make('level')->label('Level')->badge(),
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
