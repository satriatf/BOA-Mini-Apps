<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Project;
use App\Models\Mtc;
use App\Models\OnLeave;
use App\Models\MasterApplication;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class ProjectReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string|\UnitEnum|null $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Project Application';
    protected static ?string $title = 'Project Application';
    protected string $view = 'filament.pages.project-report';

    public $startMonth;
    public $startYear;
    public $endMonth;
    public $endYear;

    public function mount()
    {
        $this->startMonth = Request::query('startMonth', 1);
        $this->startYear = Request::query('startYear', 2025);
        $this->endMonth = Request::query('endMonth', now()->month);
        $this->endYear = Request::query('endYear', now()->year);
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

        // 3. Fetch Leaves (Cuti) overlapping the date range
        $leaves = OnLeave::whereNull('deleted_at')
            ->where(function($query) use ($startDate, $endDate) {
                // Ensuring any part of the leave overlapping the filter range is counted
                $query->where('start_date', '<=', $endDate)
                      ->where(function($inner) use ($startDate) {
                          $inner->whereNull('end_date')
                                ->orWhere('end_date', '>=', $startDate);
                      });
            })
            ->count();

        // 3. Project Status Distribution (Workload Chart)
        $workloadMap = [];
        foreach ($projects as $project) {
            $status = $project->project_status ?: 'UNKNOWN';
            $workloadMap[$status] = ($workloadMap[$status] ?? 0) + 1;
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

        // 5. Activity Percentage (Summary Categories)
        $activityMap = [
            'PROJECTS'     => $projects->count(),
            'NON-PROJECTS' => $mtcs->count(),
            'CUTI'         => $leaves,
        ];

        arsort($activityMap);
        $totalActivity = array_sum($activityMap);

        return [
            'workload' => [
                'labels' => array_keys($workloadMap),
                'data' => array_values($workloadMap),
                'total' => array_sum($workloadMap),
            ],
            'problems' => $problemsMap,
            'activities' => $activityMap,
            'totalActivity' => $totalActivity,
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
