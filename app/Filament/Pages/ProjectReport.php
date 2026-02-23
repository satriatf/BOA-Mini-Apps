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

        $appColors = [
            'Ad1forFlow'            => '#a5d8e8',
            'Ad1Access'             => '#fecaca',
            'Ad1Internship'         => '#fde68a',
            'QPC'                   => '#bbf7d0',
            'Ad1Falcon'             => '#ddd6fe',
            'BPKBLib'               => '#fed7aa',
            'Ad1Primajaga'          => '#99f6e4',
            'Ihtisar Asuransi'      => '#fbcfe8',
            'Public Access'         => '#d9f99d',
            'Secure Access'         => '#e2e8f0',
            'Ad1Suite'              => '#fef3c7',
            'Ad1Dis'                => '#e9d5ff',
            'Service Desk'          => '#dcfce7',
            'Ivanti Service Desk'   => '#fee2e2',
            'Smile Apps'            => '#fef08a',
            'E Recruitment'         => '#cffafe',
            'WA Message Generator'  => '#a7f3d0',
            'ECM'                   => '#f5f5f4',
            'Digilearn'             => '#c7d2fe',
            'Digilearn Keday'       => '#ffedd5',
            'Final Riplay'          => '#f5d0fe',
            'CMS Scan BPKB'         => '#ecfccb',
            'Fiducia Console Konven'=> '#e0f2fe',
            'Fiducia Console DLB orms'=> '#f3e8ff',
            'Adirabox'              => '#ffe4e6',
            'Asset Management'      => '#ccfbf1',
            'Audit Management'      => '#fecdd3',
            'Reengenering Ad1forFlow'=> '#dbeafe',
            'Other'                 => '#f1f5f9',
        ];

        $fallbackColors = ['#d9f99d', '#99f6e4', '#ddd6fe', '#fed7aa', '#bfdbfe', '#fbcfe8', '#c7d2fe', '#cffafe'];

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
