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

    protected function getData(): array
    {
        $year = $this->filter ?? Carbon::now()->year;

        $projectCount = Project::whereYear('created_at', $year)->count();
        $mtcCount = Mtc::whereYear('created_at', $year)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Tasks',
                    'data' => [$projectCount, $mtcCount],
                    'backgroundColor' => [
                        'rgb(59, 130, 246)', // Blue for Projects
                        'rgb(234, 179, 8)',  // Yellow for Non-Projects
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(234, 179, 8)',
                    ],
                ],
            ],
            'labels' => ['Projects', 'Non-Projects'],
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
