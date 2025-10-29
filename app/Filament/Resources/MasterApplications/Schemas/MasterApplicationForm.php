<?php

namespace App\Filament\Resources\MasterApplications\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class MasterApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')
                    ),
            ]);
    }
}
