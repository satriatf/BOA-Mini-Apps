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
            ->searchPlaceholder('Search by no ticket or description')
            ->columns([
                TextColumn::make('no_tiket')
                    ->label('No. Ticket')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('deskripsi')
                    ->label('Description')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->sortable(),

                TextColumn::make('resolver.employee_name')
                    ->label('Resolver PIC')
                    ->sortable(),

                TextColumn::make('solusi')
                    ->label('Solution')
                    ->limit(60),

                TextColumn::make('application')
                    ->label('Application')
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('attachments')
                    ->label('Attachments')
                    ->state(function ($record) {
                        $files = $record->attachments;

                        if (is_string($files)) {
                            $decoded = json_decode($files, true);
                            $files = is_array($decoded) ? $decoded : [$files];
                        } elseif (!is_array($files)) {
                            $files = [];
                        }

                        if (empty($files)) {
                            return '0'; 
                        }

                        $lines = collect($files)->map(function ($item) {
                            $path = is_array($item) && isset($item['path'])
                                ? $item['path']
                                : (is_string($item) ? $item : null);

                            if (!$path) return null;

                            $name = basename($path);
                            $safe = e($name);
                            $safe = str_replace(' ', '&nbsp;', $safe);

                            return '<span class="whitespace-nowrap">'.$safe.'</span>';
                        })->filter()->values();

                        return $lines->implode('<br>');
                    })
                    ->html(), 
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
