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

        // Define specific colors for applications
        $appColors = [
            'Ad1forFlow' => '#3b82f6',      // Blue
            'Ad1Access' => '#ef4444',       // Red
            'Ad1Internship' => '#f59e0b',   // Amber
            'QPC' => '#10b981',             // Emerald
            'Ad1Falcon' => '#8b5cf6',       // Purple
            'BPKBLib' => '#ec4899',         // Pink
            'Ad1Primajaga' => '#06b6d4',    // Cyan
            'Ihtisar Asuransi' => '#d946ef', // Fuchsia
            'Public Access' => '#84cc16',   // Lime
            
            // Additional requested apps
            'Secure Access' => '#0f172a',    // Dark Slate
            'Ad1Suite' => '#6366f1',         // Indigo
            'Ad1Dis' => '#f97316',           // Orange
            'Service Desk' => '#14b8a6',     // Teal
            'Ivanti Service Desk' => '#0d9488', // Dark Teal
            'Smile Apps' => '#fbbf24',       // Yellow
            'E Recruitment' => '#84cc16',    // Lime 600
            'WA Message Generator' => '#25d366', // WhatsApp Green
            'ECM' => '#475569',              // Slate 600
            'Digilearn' => '#8b5cf6',        // Violet
            'Digilearn Keday' => '#7c3aed',  // Violet 700
            'Final Riplay' => '#db2777',     // Pink 600
            'CMS Scan BPKB' => '#be123c',    // Rose 700
            'Fiducia Console Konven' => '#1d4ed8', // Blue 700
            'Fiducia Console DLB orms' => '#1e40af', // Blue 800
            'Adirabox' => '#facc15',         // Yellow 400
            'Asset Management' => '#4ade80', // Green 400
            'Audit Management' => '#2dd4bf', // Teal 400
            'Reengenering Ad1forFlow' => '#2563eb', // Blue 600
            
            'Other' => '#64748b',           // Slate
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
