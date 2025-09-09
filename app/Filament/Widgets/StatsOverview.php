<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Employees', User::count())
                ->icon('heroicon-o-user-group'),

            Stat::make('Projects', Project::count())
                ->icon('heroicon-o-briefcase'),
        ];
    }
}
