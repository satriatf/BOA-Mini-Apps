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

        // For bar chart we use two datasets so the legend shows
        // separate items for Projects and Non-Projects with their colors.
        return [
            'datasets' => [
                [
                    'label' => "Projects ({$projectCount})",
                    'data' => [$projectCount],
                    'backgroundColor' => '#2563eb',
                    'borderColor' => '#2563eb',
                    'borderWidth' => 1,
                    'barPercentage' => 0.6,
                    'categoryPercentage' => 0.6,
                ],
                [
                    'label' => "Non-Projects ({$mtcCount})",
                    'data' => [$mtcCount],
                    'backgroundColor' => '#d97706',
                    'borderColor' => '#d97706',
                    'borderWidth' => 1,
                    'barPercentage' => 0.6,
                    'categoryPercentage' => 0.6,
                ],
            ],
            // Single category to place both bars side-by-side
            'labels' => ['Tasks'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    // Move legend to the bottom so long labels (with counts)
                    // are not clipped on the right side of the card.
                    'position' => 'bottom',
                    'align' => 'center',
                    'labels' => [
                        'font' => [
                            'weight' => 'bold',
                            'size' => 12,
                        ],
                        'color' => '#374151',
                        'padding' => 14,
                        // Slightly smaller color box to save horizontal space
                        'boxWidth' => 12,
                    ],
                ],
            ],
            // Provide a bit of vertical padding to comfortably fit the legend at the bottom
            'layout' => [
                'padding' => [
                    'top' => 4,
                    'bottom' => 6,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                    'grid' => [
                        'color' => 'rgba(107,114,128,0.15)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
