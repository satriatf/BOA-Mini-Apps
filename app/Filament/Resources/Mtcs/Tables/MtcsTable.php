<?php

namespace App\Filament\Resources\Mtcs\Tables;

use App\Models\MasterNonProjectType;
use App\Models\MasterApplication;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(fn () => MasterNonProjectType::pluck('name', 'name')->toArray())
                    ->searchable()
                    ->indicator('Type'),

                SelectFilter::make('resolver_id')
                    ->label('Resolver PIC')
                    ->options(fn () => User::where('is_active', 'Active')->whereIn('level', ['Staff', 'Section Head'])->pluck('employee_name', 'sk_user')->toArray())
                    ->searchable()
                    ->indicator('Resolver'),

                SelectFilter::make('application')
                    ->label('Application')
                    ->options(fn () => MasterApplication::pluck('name', 'name')->toArray())
                    ->searchable()
                    ->indicator('Application'),

                Filter::make('tanggal')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('from')->label('Date from'),
                        DatePicker::make('to')->label('Date to'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal', '>=', $date))
                            ->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('tanggal', '<=', $date));
                    }),
            ])
            ->filtersTriggerAction(fn ($action) => $action
                ->button()
                ->label('Filter')
                ->icon('heroicon-o-funnel'),
            )
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
