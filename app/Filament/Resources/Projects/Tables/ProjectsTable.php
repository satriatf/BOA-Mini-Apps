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
            ->columns([
                TextColumn::make('pmo_id')
                    ->label('PMO ID')
                    ->searchable(),

                TextColumn::make('project_name')
                    ->label('Project Name')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->searchable(),

                TextColumn::make('techLead.name')
                    ->label('Tech Lead'),

                TextColumn::make('pics')
                    ->label('PIC')
                    ->state(function ($record) {
                        $names = $record->pic_users->pluck('name')->values()->all();
                        if (empty($names)) {
                            return '-';
                        }

                        $lines = [];
                        foreach ($names as $i => $name) {
                            // gabungkan nomor + nama, lalu ubah spasi jadi non-breaking space
                            $full = ($i + 1) . '. ' . $name;
                            $safe = e($full);
                            $safe = str_replace(' ', '&nbsp;', $safe);

                            $lines[] = $safe;
                        }

                        // tiap nama dipisah <br> → tampil ke bawah
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

                TextColumn::make('days')
                    ->label('Days')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('percent_done')
                    ->label('% Done')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state . '%'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
