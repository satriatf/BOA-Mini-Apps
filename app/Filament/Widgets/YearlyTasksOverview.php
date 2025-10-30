<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Mtc;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class YearlyTasksOverview extends BaseWidget
{
    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        $currentYear = Carbon::now()->year;
        $years = [];
        
        // Generate years from 2020 to current year + 2
        for ($year = 2020; $year <= $currentYear + 2; $year++) {
            $years[$year] = $year;
        }
        
        return $years;
    }

    protected function getStats(): array
    {
        $year = $this->filter ?? Carbon::now()->year;

        $projectCount = Project::whereYear('created_at', $year)->count();
        $mtcCount = Mtc::whereYear('created_at', $year)->count();

        return [
            Stat::make('Projects', $projectCount)
                ->description("Total in {$year}")
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Non-Projects', $mtcCount)
                ->description("Total in {$year}")
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
        ];
    }
}
