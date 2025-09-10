<?php

namespace App\Filament\Resources\Mtcs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MtcsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deskripsi')
                    ->label('Description')
                    ->limit(60),

                TextColumn::make('type')
                    ->label('Ticket Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('resolver.name')
                    ->label('Resolver PIC')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('solusi')
                    ->label('Solution')
                    ->limit(60),

                TextColumn::make('application')
                    ->label('Application')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('attachments_count')
                    ->label('Attachments')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                // optional: tambahkan filter kalau perlu
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
