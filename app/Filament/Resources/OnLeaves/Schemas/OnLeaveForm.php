<?php

namespace App\Filament\Resources\OnLeaves\Schemas;

use App\Models\MasterLeaveType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class OnLeaveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('user_id')
                ->default(fn () => Auth::id()),

            Select::make('leave_type')
                ->label('Leave Type')
                ->options(fn () => MasterLeaveType::whereNull('deleted_at')->pluck('name', 'name'))
                ->searchable()
                ->native(false)
                ->required(),

            DatePicker::make('start_date')
                ->label('Start Date')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection()
                ->live()
                ->afterStateUpdated(function ($state, callable $set, $get) {
                    if ($get('end_date') && $state && $get('end_date') < $state) {
                        $set('end_date', null);
                    }
                }),

            DatePicker::make('end_date')
                ->label('End Date')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y')
                ->minDate(fn ($get) => $get('start_date'))
                ->closeOnDateSelection()
                ->live()
                ->disabled(fn ($get) => !$get('start_date'))
                ->rules(['after_or_equal:start_date']),
        ]);
    }
}
