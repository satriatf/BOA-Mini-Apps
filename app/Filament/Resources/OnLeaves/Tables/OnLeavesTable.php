<?php

namespace App\Filament\Resources\OnLeaves\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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
                //
            ])
            ->recordUrl(null)
            ->recordActions([
                DeleteAction::make()
                    ->label('Delete')           // label actions
                    ->icon('heroicon-o-trash')
                    ->iconButton()

                    // 👇 heading modal pakai nama user, bukan id
                    ->modalHeading(function ($record) {
                        $userName = $record->user->employee_name
                            ?? $record->user->name
                            ?? 'this leave';

                        return 'Delete ' . $userName;
                    })

                    ->modalDescription('Are you sure you want to delete this leave?')

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
