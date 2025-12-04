<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Project;
use App\Models\Mtc;
use App\Models\MasterApplication;
use App\Models\Holiday;
use App\Models\OnLeave;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            // Exclude script/admin users from the Employees stat
            Stat::make('Employees', User::where(function ($q) { $q->where('is_admin', false)->orWhereNull('is_admin'); })->count())
                ->icon('heroicon-o-user-group'),

            Stat::make('Projects', Project::count())
                ->icon('heroicon-o-window'),

            Stat::make('Non-Projects', Mtc::count())
                ->icon('heroicon-o-code-bracket-square'),

            Stat::make('On Leaves', OnLeave::count())
                ->icon('heroicon-o-user'),

            Stat::make('Applications', MasterApplication::count())
                ->icon('heroicon-o-squares-2x2'),

            Stat::make('Holidays', Holiday::count())
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
