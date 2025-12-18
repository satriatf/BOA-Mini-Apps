<?php

namespace App\Filament\Resources\MasterApplications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MasterApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search by name')
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_by')
                    ->label('Created By')
                    ->getStateUsing(fn ($record) => $record->created_by ?: '-')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordUrl(null)
            ->recordActions([
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->modalHeading(function ($record) {
                        $name = $record->name ?? 'this application';
                        return 'DELETE "' . $name . '"';
                    })
                    ->modalDescription('Are you sure you would like to do this?'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
