<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nik')->required()->unique(ignoreRecord: true),
                TextInput::make('name')->required(),
                Select::make('is_active')->options(['Active' => 'Active', 'Inactive' => 'Inactive'])->required(),
                DatePicker::make('join_date'),
                DatePicker::make('end_date'),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->required(fn($record) => $record === null) // wajib saat create
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->label('Password'),
            ]);
    }
}
