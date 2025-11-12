<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Unique;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isEditingAdmin = function (): bool {
            $routeParam = request()->route('record') ?? request()->route('recordId') ?? null;
            if (! $routeParam) {
                return false;
            }

            $user = \App\Models\User::find($routeParam);
            return $user?->is_admin ?? false;
        };

        return $schema->components([
    TextInput::make('employee_nik')
        ->label('Employee NIK')
        ->required()
        ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')),

    TextInput::make('employee_name')
        ->label('Employee Name')
        ->required(),

    TextInput::make('employee_email')
        ->label('Employee Email')
        ->required()
        ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at'))
        ->suffix('@adira.co.id')
        ->dehydrateStateUsing(fn ($state) => $state . '@adira.co.id')
        ->afterStateHydrated(function ($component, $state) {
            if ($state && str_contains($state, '@adira.co.id')) {
                $component->state(str_replace('@adira.co.id', '', $state));
            }
        })
        ->rules(['regex:/^[a-zA-Z0-9._-]+$/']),

    Select::make('level') 
        ->label('Level')
        ->options([
            'Manager'  => 'Manager',
            'Asisten Manager' => 'Asisten Manager',
            'Section Head' => 'Section Head',
            'Staff'    => 'Staff',
            'Intern'   => 'Intern',
        ])
        ->placeholder('Select one')
        ->required(fn () => ! ($isEditingAdmin)())
        ->hidden(fn () => ($isEditingAdmin)()),

    Select::make('is_active')
        ->label('Is Active')
        ->options([
            'Active' => 'Active',
            'Inactive' => 'Inactive',
        ])
        ->placeholder('Select status')
        ->dehydrated() // Ensure this field is always included in form data
        ->required(),

    DatePicker::make('join_date')
        ->label('Join Date')
        ->native(false)
        ->displayFormat('d/m/Y')
        ->closeOnDateSelection()
        ->live()
        ->afterStateUpdated(function ($state, callable $set, $get) {
            // Reset end_date if it's less than join_date
            if ($get('end_date') && $state && $get('end_date') < $state) {
                $set('end_date', null);
            }
        }),

    DatePicker::make('end_date')
        ->label('End Date')
        ->native(false)
        ->displayFormat('d/m/Y')
        ->minDate(fn ($get) => $get('join_date'))
        ->closeOnDateSelection()
        ->live()
        ->disabled(fn ($get) => !$get('join_date'))
        ->rules(['after_or_equal:join_date']),

    TextInput::make('password')
        ->label('Password')
        ->password()
        ->revealable()
        ->required(fn (string $operation) => $operation === 'create')
        ->dehydrated(fn ($state) => filled($state))
]);
    }
}
