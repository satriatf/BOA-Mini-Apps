<?php

namespace App\Filament\Resources\OnLeaves\Pages;

use App\Filament\Resources\OnLeaves\OnLeaveResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditOnLeave extends EditRecord
{
    protected static string $resource = OnLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Delete')
                ->modalHeading(function ($record) {
                    $userName = $record->user->employee_name
                        ?? $record->user->name
                        ?? 'this leave';

                    return 'DELETE "' . $userName . '"';
                })
                ->modalDescription('Are you sure you would like to do this?')
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
                        // silently ignore error, delete still proceeds
                    }

                    $record->delete();
                }),
        ];
    }
}
