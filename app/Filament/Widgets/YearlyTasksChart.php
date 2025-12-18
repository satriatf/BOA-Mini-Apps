<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Mtc;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class YearlyTasksChart extends ChartWidget
{
    protected int | string | array $columnSpan = 1;

    protected ?string $maxHeight = '450px';
    
    protected ?string $minHeight = '400px';

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

        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        $projectCount = Project::where(function ($q) use ($year, $yearStart, $yearEnd) {
            $q->whereYear('start_date', $year)
                ->orWhereYear('end_date', $year)
                ->orWhere(function ($q2) use ($yearStart, $yearEnd) {
                    $q2->whereNotNull('start_date')
                        ->whereNotNull('end_date')
                        ->whereDate('start_date', '<=', $yearEnd)
                        ->whereDate('end_date', '>=', $yearStart);
                });
        })
            ->whereNull('deleted_at')
            ->count();

        $mtcCount = Mtc::whereYear('tanggal', $year)
            ->whereNull('deleted_at')
            ->count();

        // Calculate max value across all years to set consistent Y-axis
        $maxProjects = Project::selectRaw('EXTRACT(YEAR FROM COALESCE(start_date, end_date)) as year, COUNT(*) as count')
            ->where(function ($q) {
                $q->whereNotNull('start_date')
                    ->orWhereNotNull('end_date');
            })
            ->whereNull('deleted_at')
            ->groupBy('year')
            ->orderByDesc('count')
            ->first()?->count ?? 0;

        $maxMtcs = Mtc::selectRaw('EXTRACT(YEAR FROM tanggal) as year, COUNT(*) as count')
            ->whereNotNull('tanggal')
            ->whereNull('deleted_at')
            ->groupBy('year')
            ->orderByDesc('count')
            ->first()?->count ?? 0;

        $suggestedMax = 100;

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
            'suggestedMax' => $suggestedMax,
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
            'responsive' => true,
            'aspectRatio' => 0.8,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'align' => 'center',
                    'labels' => [
                        'font' => [
                            'weight' => 'bold',
                            'size' => 12,
                        ],
                        'color' => '#374151',
                        'padding' => 14,
                        'boxWidth' => 12,
                    ],
                ],
            ],
            'layout' => [
                'padding' => [
                    'top' => 20,
                    'bottom' => 15,
                    'left' => 25,
                    'right' => 20,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,
                    'max' => 100,
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 10,
                        'autoSkip' => false,
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