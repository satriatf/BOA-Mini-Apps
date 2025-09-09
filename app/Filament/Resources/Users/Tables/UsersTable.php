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
                TextColumn::make('nik')->label('NIK')->searchable(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('is_active')->label('Is Active')->badge(),
                TextColumn::make('join_date')->label('Join Date')->date(),
                TextColumn::make('end_date')->label('End Date')->date(),
                TextColumn::make('email')->label('Email')->searchable(),
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
