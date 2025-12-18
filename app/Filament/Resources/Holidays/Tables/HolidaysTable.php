<?php

namespace App\Filament\Resources\Holidays\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search by description')
            ->columns([
                TextColumn::make('date')
                    ->label('Holiday Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('desc')
                    ->label('Description')
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
                        $desc = $record->desc ?? 'this holiday';
                        return 'DELETE "' . $desc . '"';
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
