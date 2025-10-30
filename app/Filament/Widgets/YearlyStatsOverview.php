<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Mtc;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Forms\Components\Select;
use Illuminate\Support\Carbon;

class YearlyStatsOverview extends BaseWidget
{
    protected function getHeading(): ?string
    {
        return 'Yearly Statistics';
    }

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
            Stat::make("Projects in {$year}", $projectCount)
                ->icon('heroicon-o-briefcase')
                ->description('Total projects created this year')
                ->color('success'),

            Stat::make("Non-Projects in {$year}", $mtcCount)
                ->icon('heroicon-o-wrench-screwdriver')
                ->description('Total non-projects created this year')
                ->color('info'),
        ];
    }
}
