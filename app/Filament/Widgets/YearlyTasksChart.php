<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Mtc;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class YearlyTasksChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Tasks Overview';
    }

    public ?string $filter = null;

    public function mount(): void
    {
        $this->filter = (string) Carbon::now()->year;
    }

    protected function getFilters(): ?array
    {
        $currentYear = Carbon::now()->year;
        $years = [];
        
        for ($year = 2022; $year <= $currentYear + 5; $year++) {
            $years[$year] = $year;
        }
        
        return $years;
    }

    protected function getData(): array
    {
        $year = $this->filter ?? Carbon::now()->year;

        $projectCount = Project::whereYear('start_date', $year)
            ->whereNull('deleted_at')
            ->count();
        
        $mtcCount = Mtc::whereYear('tanggal', $year)
            ->whereNull('deleted_at')
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Tasks',
                    'data' => [$projectCount, $mtcCount],
                    'backgroundColor' => [
                        '#2563eb', // Blue for Projects
                        '#d97706',  // Yellow for Non-Projects
                    ],
                    'borderColor' => [
                        '#2563eb',
                        '#d97706',
                    ],
                ],
            ],
            'labels' => ["Projects ({$projectCount})", "Non-Projects ({$mtcCount})"],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
        ];
    }
}
