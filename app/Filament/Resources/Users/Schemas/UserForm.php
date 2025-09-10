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
return $schema->components([
    TextInput::make('nik')
        ->label('NIK')
        ->required()
        ->unique(ignoreRecord: true),

    TextInput::make('name')
        ->label('Name')
        ->required(),

    Select::make('level') 
        ->label('Level')
        ->options([
            'Manager'  => 'Manager',
            'Asmen' => 'Asmen',
            'SH' => 'SH',
            'Staff'    => 'Staff',
            'Intern'   => 'Intern',
        ])
        ->placeholder('Select one')
        ->required(),

    Select::make('is_active')
        ->label('Is Active')
        ->options([
            'Active' => 'Active',
            'Inactive' => 'Inactive',
        ])
        ->required(),

    DatePicker::make('join_date')
        ->label('Join Date')
        ->native(false)
        ->displayFormat('d/m/Y'),

    DatePicker::make('end_date')
        ->label('End Date')
        ->native(false)
        ->displayFormat('d/m/Y'),

    TextInput::make('email')
        ->label('Email')
        ->email()
        ->required()
        ->unique(ignoreRecord: true),

    TextInput::make('password')
        ->label('Password')
        ->password()
        ->revealable()

        ->required(fn (string $operation) => $operation === 'create')

        ->dehydrated(fn ($state) => filled($state))
]);
    }
}
