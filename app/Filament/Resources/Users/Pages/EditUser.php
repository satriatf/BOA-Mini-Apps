<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Delete')
                ->modalHeading(function ($record) {
                    $name = $record->employee_name ?? $record->name ?? 'this user';
                    return 'DELETE "' . $name . '"';
                })
                ->modalDescription('Are you sure you would like to do this?'),
        ];
    }

    public function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }
    // ...existing code...
}
