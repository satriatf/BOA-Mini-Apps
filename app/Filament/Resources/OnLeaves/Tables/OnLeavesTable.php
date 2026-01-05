<?php

namespace App\Filament\Resources\OnLeaves\Tables;

use App\Models\MasterLeaveType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OnLeavesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search by user')
            ->columns([
                TextColumn::make('user.employee_name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('leave_type')
                    ->label('Leave Type')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('search_by')
                    ->label('Filter By')
                    ->form([
                        Select::make('field')
                            ->label('')
                            ->options([
                                'user_name' => 'Name',
                                'leave_type' => 'Leave Type',
                                'start_date' => 'Start Date',
                                'end_date' => 'End Date',
                            ])
                            ->placeholder('Select field to filter')
                            ->reactive(),
                        TextInput::make('search_value')
                            ->label('Search By')
                            ->placeholder(function ($get) {
                                $field = $get('field');
                                return match ($field) {
                                    'user_name' => 'Search by Name...',
                                    default => 'Type to search...',
                                };
                            })
                            ->visible(fn ($get) => $get('field') === 'user_name'),
                        Select::make('leave_type_value')
                            ->label('Select By')
                            ->options(fn () => MasterLeaveType::pluck('name', 'name')->toArray())
                            ->searchable()
                            ->placeholder('Select by Leave Type...')
                            ->visible(fn ($get) => $get('field') === 'leave_type'),
                        DatePicker::make('start_date_value')
                            ->label('Select By')
                            ->native(false)
                            ->placeholder('Select by Start Date...')
                            ->visible(fn ($get) => $get('field') === 'start_date'),
                        DatePicker::make('end_date_value')
                            ->label('Select By')
                            ->native(false)
                            ->placeholder('Select by End Date...')
                            ->visible(fn ($get) => $get('field') === 'end_date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $field = $data['field'] ?? null;
                        $searchValue = $data['search_value'] ?? null;
                        $leaveTypeValue = $data['leave_type_value'] ?? null;
                        $startDateValue = $data['start_date_value'] ?? null;
                        $endDateValue = $data['end_date_value'] ?? null;

                        if (!$field) {
                            return $query;
                        }

                        return match ($field) {
                            'user_name' => $searchValue
                                ? $query->whereHas('user', function (Builder $q) use ($searchValue) {
                                    $q->whereRaw('LOWER(employee_name) LIKE ?', ['%' . strtolower($searchValue) . '%']);
                                })
                                : $query,
                            'leave_type' => $leaveTypeValue
                                ? $query->where('leave_type', $leaveTypeValue)
                                : $query,
                            'start_date' => $startDateValue
                                ? $query->whereDate('start_date', $startDateValue)
                                : $query,
                            'end_date' => $endDateValue
                                ? $query->whereDate('end_date', $endDateValue)
                                : $query,
                            default => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $field = $data['field'] ?? null;
                        $searchValue = $data['search_value'] ?? null;
                        $leaveTypeValue = $data['leave_type_value'] ?? null;
                        $startDateValue = $data['start_date_value'] ?? null;
                        $endDateValue = $data['end_date_value'] ?? null;

                        if (!$field) {
                            return null;
                        }

                        $fieldLabels = [
                            'user_name' => 'Name',
                            'leave_type' => 'Leave Type',
                            'start_date' => 'Start Date',
                            'end_date' => 'End Date',
                        ];

                        if ($field === 'leave_type') {
                            $value = $leaveTypeValue;
                        } elseif ($field === 'start_date') {
                            $value = $startDateValue;
                        } elseif ($field === 'end_date') {
                            $value = $endDateValue;
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
            ->recordUrl(null)
            ->recordActions([
                DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->iconButton()
                    ->modalHeading(function ($record) {
                        $userName = $record->user->employee_name
                            ?? $record->user->name
                            ?? 'this leave';

                        return 'DELETE "' . $userName . '"';
                    })
                    ->modalDescription('Are you sure you would like to do this?')

                    // simpan deleted_by pakai NAMA user login, lalu soft delete
                    ->action(function ($record) {
                        try {
                            if (Auth::check()) {
                                $name = Auth::user()->employee_name
                                    ?? Auth::user()->name
                                    ?? null;

                                if ($name) {
                                    $record->deleted_by = $name;
                                    $record->save();
                                }
                            }
                        } catch (\Throwable $e) {
                            // biarkan error diam-diam, delete tetap jalan
                        }

                        $record->delete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete selected') // biar nggak "Delete 1"
                        ->modalHeading('Delete selected leaves'),
                ]),
            ]);
    }
}
