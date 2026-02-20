<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;
use App\Models\Mtc;
use App\Models\MasterApplication;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class ProjectReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string|\UnitEnum|null $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Application Report';
    protected static ?string $title = 'Application Report';
    protected string $view = 'filament.pages.project-report';

    public $startMonth;
    public $startYear;
    public $endMonth;
    public $endYear;

    public function mount()
    {
        $this->startMonth = request('startMonth', now()->month);
        $this->startYear = request('startYear', now()->year);
        $this->endMonth = request('endMonth', now()->month);
        $this->endYear = request('endYear', now()->year);

        // Ensure variables are integers
        $this->startMonth = (int) $this->startMonth;
        $this->startYear = (int) $this->startYear;
        $this->endMonth = (int) $this->endMonth;
        $this->endYear = (int) $this->endYear;
    }

    public function getChartData(): array
    {
        $startDate = Carbon::create($this->startYear, $this->startMonth, 1)->startOfMonth();
        $endDate = Carbon::create($this->endYear, $this->endMonth, 1)->endOfMonth();

        // 1. Fetch active projects overlapping the date range
        $projects = Project::where('is_delete', false)
            ->whereNull('deleted_at')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where(function($inner) use ($startDate) {
                          $inner->whereNull('end_date')
                                ->orWhere('end_date', '>=', $startDate);
                      });
            })
            ->get();

        // 2. Fetch Mtc (Non-Projects) within range
        $mtcs = Mtc::where('is_delete', false)
            ->whereNull('deleted_at')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();


        // 3. Project Application Distribution (Workload Chart)
        $workloadMap = [];
        foreach ($projects as $project) {
            $app = $project->application;
            if (!empty($app)) {
                $workloadMap[$app] = ($workloadMap[$app] ?? 0) + 1;
            }
        }
        arsort($workloadMap);

        // 4. Application Problems (Mtc)
        $masterApps = MasterApplication::pluck('name')->toArray();
        $problemsMap = [];
        
        foreach ($mtcs as $mtc) {
            $rawApp = trim($mtc->application) ?: 'Other';
            $resolvedName = $rawApp;
            
            // Try to resolve name from master applications for consistent casing
            foreach ($masterApps as $masterApp) {
                if (strcasecmp($rawApp, $masterApp) === 0) {
                    $resolvedName = $masterApp;
                    break;
                }
            }
            
            $problemsMap[$resolvedName] = ($problemsMap[$resolvedName] ?? 0) + 1;
        }
        
        arsort($problemsMap);

        // Define specific colors for applications to ensure no clashing
        $appColors = [
            'Ad1forFlow'            => '#60a5fa', // Blue
            'Ad1Access'             => '#f87171', // Red
            'Ad1Internship'         => '#fbbf24', // Amber
            'QPC'                   => '#34d399', // Emerald
            'Ad1Falcon'             => '#a78bfa', // Violet
            'BPKBLib'               => '#fb923c', // Orange
            'Ad1Primajaga'          => '#2dd4bf', // Teal
            'Ihtisar Asuransi'      => '#f472b6', // Pink
            'Public Access'         => '#a3e635', // Lime
            
            // Additional requested apps with distinct colors
            'Secure Access'         => '#94a3b8', // Slate Gray
            'Ad1Suite'              => '#fde047', // Yellow
            'Ad1Dis'                => '#c084fc', // Purple
            'Service Desk'          => '#86efac', // Mint Green
            'Ivanti Service Desk'   => '#fca5a5', // Salmon
            'Smile Apps'            => '#fcd34d', // Gold
            'E Recruitment'         => '#67e8f9', // Cyan
            'WA Message Generator'  => '#4ade80', // Green
            'ECM'                   => '#d6d3d1', // Stone
            'Digilearn'             => '#818cf8', // Indigo
            'Digilearn Keday'       => '#fdba74', // Peach
            'Final Riplay'          => '#e879f9', // Magenta
            'CMS Scan BPKB'         => '#bef264', // Yellow Green
            'Fiducia Console Konven' => '#7dd3fc', // Sky Blue
            'Fiducia Console DLB orms' => '#d8b4fe', // Lavender
            'Adirabox'              => '#fda4af', // Rose
            'Asset Management'      => '#5eead4', // Aqua
            'Audit Management'      => '#fca5a1', // Coral
            'Reengenering Ad1forFlow' => '#93c5fd', // Periwinkle
            
            'Other'                 => '#cbd5e1', // Silver
        ];

        // Fallback palette for unknown apps
        $fallbackColors = ['#84cc16', '#14b8a6', '#d946ef', '#f97316', '#6366f1', '#f43f5e', '#8b5cf6', '#06b6d4'];

        $getColors = function($labels) use ($appColors, $fallbackColors) {
            $colors = [];
            $fallbackIndex = 0;
            foreach ($labels as $label) {
                if (isset($appColors[$label])) {
                    $colors[] = $appColors[$label];
                } else {
                    $colors[] = $fallbackColors[$fallbackIndex % count($fallbackColors)];
                    $fallbackIndex++;
                }
            }
            return $colors;
        };

        $workloadLabels = array_keys($workloadMap);
        $nonProjectLabels = array_keys($problemsMap);

        return [
            'workload' => [
                'labels' => $workloadLabels,
                'data' => array_values($workloadMap),
                'colors' => $getColors($workloadLabels),
                'total' => array_sum($workloadMap),
            ],
            'nonProjectWorkload' => [
                'labels' => $nonProjectLabels,
                'data' => array_values($problemsMap),
                'colors' => $getColors($nonProjectLabels),
                'total' => array_sum($problemsMap),
            ],
        ];
    }

    protected function getViewData(): array
    {
        return [
            'reportData' => $this->getChartData(),
            'startMonth' => $this->startMonth,
            'startYear'  => $this->startYear,
            'endMonth'   => $this->endMonth,
            'endYear'    => $this->endYear,
        ];
    }
}
