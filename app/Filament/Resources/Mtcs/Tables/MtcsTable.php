<?php

namespace App\Filament\Resources\Mtcs\Tables;

use App\Models\MasterNonProjectType;
use App\Models\MasterApplication;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
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
                // Filter By List & Search By
                Filter::make('search_by')
                    ->label('Filter By')
                    ->form([
                        Select::make('field')
                            ->label('Select By')
                            ->options([
                                'no_tiket' => 'No. Ticket',
                                'deskripsi' => 'Description',
                                'type' => 'Type',
                                'resolver_id' => 'Resolver PIC',
                                'solusi' => 'Solution',
                                'application' => 'Application',
                                'tanggal' => 'Date',
                            ])
                            ->placeholder('Select field to filter')
                            ->reactive(),
                        TextInput::make('search_value')
                            ->label('Search By')
                            ->placeholder(function ($get) {
                                $field = $get('field');
                                return match ($field) {
                                    'no_tiket' => 'Search by No. Ticket...',
                                    'deskripsi' => 'Search by Description...',
                                    'solusi' => 'Search by Solution...',
                                    default => 'Type to search...',
                                };
                            })
                            ->visible(fn ($get) => in_array($get('field'), [
                                'no_tiket', 'deskripsi', 'solusi'
                            ])),
                        Select::make('type_value')
                            ->label('Select By')
                            ->options(fn () => MasterNonProjectType::pluck('name', 'name')->toArray())
                            ->searchable()
                            ->placeholder('Select by Type...')
                            ->visible(fn ($get) => $get('field') === 'type'),
                        Select::make('resolver_value')
                            ->label('Select By')
                            ->options(fn () => User::where('is_active', 'Active')
                                ->whereNotNull('employee_name')
                                ->whereIn('level', ['STAFF', 'SH', 'Staff', 'Section Head'])
                                ->orderBy('employee_name')
                                ->pluck('employee_name', 'sk_user')
                                ->toArray())
                            ->searchable()
                            ->placeholder('Select by Resolver PIC...')
                            ->visible(fn ($get) => $get('field') === 'resolver_id'),
                        Select::make('application_value')
                            ->label('Select By')
                            ->options(fn () => MasterApplication::pluck('name', 'name')->toArray())
                            ->searchable()
                            ->placeholder('Select by Application...')
                            ->visible(fn ($get) => $get('field') === 'application'),
                        DatePicker::make('date_value')
                            ->label('Select By')
                            ->native(false)
                            ->placeholder('Select by Date...')
                            ->visible(fn ($get) => $get('field') === 'tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $field = $data['field'] ?? null;
                        $searchValue = $data['search_value'] ?? null;
                        $typeValue = $data['type_value'] ?? null;
                        $resolverValue = $data['resolver_value'] ?? null;
                        $applicationValue = $data['application_value'] ?? null;
                        $dateValue = $data['date_value'] ?? null;

                        if (!$field) {
                            return $query;
                        }

                        return match ($field) {
                            'no_tiket' => $query->when(
                                $searchValue,
                                fn (Builder $q) => $q->whereRaw('LOWER(no_tiket) LIKE ?', ['%' . strtolower($searchValue) . '%'])
                            ),
                            'deskripsi' => $query->when(
                                $searchValue,
                                fn (Builder $q) => $q->whereRaw('LOWER(deskripsi) LIKE ?', ['%' . strtolower($searchValue) . '%'])
                            ),
                            'type' => $query->when(
                                $typeValue,
                                fn (Builder $q) => $q->where('type', $typeValue)
                            ),
                            'resolver_id' => $query->when(
                                $resolverValue,
                                fn (Builder $q) => $q->where('resolver_id', $resolverValue)
                            ),
                            'solusi' => $query->when(
                                $searchValue,
                                fn (Builder $q) => $q->whereRaw('LOWER(solusi) LIKE ?', ['%' . strtolower($searchValue) . '%'])
                            ),
                            'application' => $query->when(
                                $applicationValue,
                                fn (Builder $q) => $q->where('application', $applicationValue)
                            ),
                            'tanggal' => $query->when(
                                $dateValue,
                                fn (Builder $q) => $q->whereDate('tanggal', $dateValue)
                            ),
                            default => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $field = $data['field'] ?? null;
                        $searchValue = $data['search_value'] ?? null;
                        $typeValue = $data['type_value'] ?? null;
                        $resolverValue = $data['resolver_value'] ?? null;
                        $applicationValue = $data['application_value'] ?? null;
                        $dateValue = $data['date_value'] ?? null;

                        if (!$field) {
                            return null;
                        }

                        $fieldLabels = [
                            'no_tiket' => 'No. Ticket',
                            'deskripsi' => 'Description',
                            'type' => 'Type',
                            'resolver_id' => 'Resolver PIC',
                            'solusi' => 'Solution',
                            'application' => 'Application',
                            'tanggal' => 'Date',
                        ];

                        if ($field === 'type') {
                            $value = $typeValue;
                        } elseif ($field === 'resolver_id') {
                            $value = $resolverValue ? User::find($resolverValue)?->employee_name : null;
                        } elseif ($field === 'application') {
                            $value = $applicationValue;
                        } elseif ($field === 'tanggal') {
                            $value = $dateValue;
                        } else {
                            $value = $searchValue;
                        }
                        
                        if ($value) {
                            return $fieldLabels[$field] . ': ' . $value;
                        }

                        return null;
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
