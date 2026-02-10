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
        $this->startYear = Request::query('startYear', now()->year);
        $this->endMonth = Request::query('endMonth', now()->month);
        $this->endYear = Request::query('endYear', now()->year);
    }

    public function getChartData(): array
    {
        $startDate = Carbon::create($this->startYear, $this->startMonth, 1)->startOfMonth();
        $endDate = Carbon::create($this->endYear, $this->endMonth, 1)->endOfMonth();

        // 1. Fetch all active projects (not deleted)
        $projects = Project::where('is_delete', false)
            ->whereNull('deleted_at')
            ->get();

        // 2. Fetch Mtc & Leaves
        $mtcs = Mtc::whereBetween('tanggal', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->get();

        $leaves = OnLeave::where(function($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
        })->count();

        // 3. Project Status Distribution (Workload Chart)
        $workloadMap = [];
        foreach ($projects as $project) {
            $status = $project->project_status ?: 'UNKNOWN';
            $workloadMap[$status] = ($workloadMap[$status] ?? 0) + 1;
        }
        arsort($workloadMap);

        // 4. Application Problems (Mtc)
        $problemsMap = [];
        foreach ($mtcs as $mtc) {
            $app = $mtc->application ?: 'Other';
            $problemsMap[$app] = ($problemsMap[$app] ?? 0) + 1;
        }
        arsort($problemsMap);

        // 5. Activity Percentage (Now based on Project Statuses + Leaves + MTC)
        $activityMap = [
            'CUTI' => $leaves,
        ];
        
        // Add Project Statuses to Activity Map
        foreach ($projects as $project) {
            $status = $project->project_status ?: 'UNKNOWN';
            $activityMap[$status] = ($activityMap[$status] ?? 0) + 1;
        }

        // Add MTC Types (Problem, etc)
        foreach ($mtcs as $mtc) {
            $type = $mtc->type ?: 'OTHER';
            $activityMap[$type] = ($activityMap[$type] ?? 0) + 1;
        }

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
