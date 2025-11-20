<?php

namespace App\Filament\Pages;

use App\Models\Mtc;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;

class EmployeeGantt extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static string|\UnitEnum|null $navigationGroup = 'Calendar';
    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'project-timeline';
    protected string $view = 'filament.pages.employee-gantt';

    public int $year;

    public static function getNavigationLabel(): string
    {
        return 'Project Timeline';
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    public function mount(): void
    {
        $raw = request()->query('year');
        $y = is_numeric($raw) ? (int) $raw : null;
        $this->year = (!$y || $y < 1900 || $y > 2100) ? now()->year : $y;
    }

    /**
     * Get rows data for the Gantt chart
     * Returns array of employees with their tasks for the selected year
     */
    public function getRows(): array
    {
        $year = $this->year;
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        $employeeTasks = [];

        // Process Projects - include projects that have project dates OR PIC rows with dates
        $projects = Project::query()
            ->where(fn($q) => $q->whereNotNull('start_date')
                ->orWhereNotNull('end_date')
                ->orWhereHas('projectPics', fn($q2) => $q2->whereNotNull('start_date')->orWhereNotNull('end_date'))
            )
            ->get();

        foreach ($projects as $project) {
            $start = $project->start_date ? Carbon::parse($project->start_date)->startOfDay() : null;
            $end = $project->end_date ? Carbon::parse($project->end_date)->endOfDay() : null;
            
            if (!$start && !$end) continue;

            // If only one date exists, treat as single day
            if ($start && !$end) $end = $start->clone();
            if (!$start && $end) $start = $end->clone();

            // Clamp to selected year
            $start = $start->max($yearStart);
            $end = $end->min($yearEnd);

            // Skip if no overlap with selected year
            if ($start->gt($yearEnd) || $end->lt($yearStart)) continue;

            // Create task title
            $projectName = trim($project->project_name ?? "Project {$project->sk_project}");
            $ticketNo = trim((string) ($project->project_ticket_no ?? ''));
            $title = $ticketNo !== '' ? "{$projectName} [{$ticketNo}]" : "{$projectName} [NO TIKET]";

            // Add technical lead if exists
            if ($project->technical_lead) {
                $employeeId = $project->technical_lead;
                if (!isset($employeeTasks[$employeeId])) {
                    $employeeTasks[$employeeId] = [];
                }

                $employeeTasks[$employeeId][] = [
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                    'type' => 'project',
                    'title' => $title,
                    'role' => 'Technical Lead',
                    'details' => $this->getProjectDetails($project),
                ];
            }
        }

        // Process Projects - also add PICs as separate tasks using project_pics relationship
        foreach ($projects as $project) {
            // Load PIC rows from the project_pics table (soft-deleted rows are excluded by default)
            $pics = $project->projectPics()->with('user')->get();
            if ($pics->isEmpty()) continue;

            $start = $project->start_date ? Carbon::parse($project->start_date)->startOfDay() : null;
            $end = $project->end_date ? Carbon::parse($project->end_date)->endOfDay() : null;
            
            if (!$start && !$end) continue;

            // If only one date exists, treat as single day
            if ($start && !$end) $end = $start->clone();
            if (!$start && $end) $start = $end->clone();

            // Clamp to selected year
            $start = $start->max($yearStart);
            $end = $end->min($yearEnd);

            // Skip if no overlap with selected year
            if ($start->gt($yearEnd) || $end->lt($yearStart)) continue;

            // Create task title
            $projectName = trim($project->project_name ?? "Project {$project->sk_project}");
            $ticketNo = trim((string) ($project->project_ticket_no ?? ''));
            $title = $ticketNo !== '' ? "{$projectName} [{$ticketNo}]" : "{$projectName} [NO TIKET]";

            foreach ($pics as $pic) {
                if (!$pic->user) continue; // skip if user missing

                // Prefer PIC-specific dates; fall back to project dates
                $picStart = $pic->start_date ? Carbon::parse($pic->start_date)->startOfDay() : ($start ? $start->clone() : null);
                $picEnd = $pic->end_date ? Carbon::parse($pic->end_date)->endOfDay() : ($end ? $end->clone() : null);

                if (!$picStart && !$picEnd) continue;

                // If only one date exists, treat as single day
                if ($picStart && !$picEnd) $picEnd = $picStart->clone();
                if (!$picStart && $picEnd) $picStart = $picEnd->clone();

                // Clamp to selected year per PIC
                $picStartClamped = $picStart->max($yearStart);
                $picEndClamped = $picEnd->min($yearEnd);

                // Skip if no overlap with selected year
                if ($picStartClamped->gt($yearEnd) || $picEndClamped->lt($yearStart)) continue;

                $employeeId = $pic->user->sk_user;
                if (!isset($employeeTasks[$employeeId])) {
                    $employeeTasks[$employeeId] = [];
                }

                $employeeTasks[$employeeId][] = [
                    'start' => $picStartClamped->toDateString(),
                    'end' => $picEndClamped->toDateString(),
                    'type' => 'project',
                    'title' => $title,
                    'role' => 'PIC',
                    'details' => $this->getProjectDetails($project),
                ];
            }
        }

        // Process MTC records - each contributes one single-day task
        $mtcs = Mtc::query()
            ->whereYear('tanggal', $year)
            ->whereNotNull('tanggal')
            ->get();

        foreach ($mtcs as $mtc) {
            $tanggal = Carbon::parse($mtc->tanggal)->startOfDay();
            
            // Skip if outside selected year
            if ($tanggal->lt($yearStart) || $tanggal->gt($yearEnd)) continue;

            // Create task title
            $application = trim($mtc->application ?? 'Non-Project');
            $ticketNo = trim((string) ($mtc->no_tiket ?? ''));
            $title = $ticketNo !== '' ? "{$application} [{$ticketNo}]" : "{$application} [NO TIKET]";

            // Add resolver if exists
            if ($mtc->resolver_id) {
                $employeeId = $mtc->resolver_id;
                if (!isset($employeeTasks[$employeeId])) {
                    $employeeTasks[$employeeId] = [];
                }

                $employeeTasks[$employeeId][] = [
                    'start' => $tanggal->toDateString(),
                    'end' => $tanggal->toDateString(),
                    'type' => 'mtc',
                    'title' => $title,
                    'role' => 'Resolver',
                    'details' => $this->getMtcDetails($mtc),
                ];
            }

            // Add created_by if exists and different from resolver
            if ($mtc->created_by_id && $mtc->created_by_id !== $mtc->resolver_id) {
                $employeeId = $mtc->created_by_id;
                if (!isset($employeeTasks[$employeeId])) {
                    $employeeTasks[$employeeId] = [];
                }

                $employeeTasks[$employeeId][] = [
                    'start' => $tanggal->toDateString(),
                    'end' => $tanggal->toDateString(),
                    'type' => 'mtc',
                    'title' => $title,
                    'role' => 'Created By',
                    'details' => $this->getMtcDetails($mtc),
                ];
            }
        }

        // Build final rows array with employee names - one row per employee
        $rows = [];
        foreach ($employeeTasks as $employeeId => $tasks) {
            $employee = User::find($employeeId);
            if (!$employee) continue;


            $rows[] = [
                'name' => $employee->employee_name,
                'tasks' => $tasks,
            ];
        }

        // Sort rows by employee name
        usort($rows, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $rows;
    }

    /**
     * Get project details HTML
     */
    private function getProjectDetails($project): string
    {
        $leadUser = $project->technical_lead ? User::find($project->technical_lead) : null;
        $lead = $leadUser?->employee_name ?? '—';
        $done = isset($project->percent_done) ? "{$project->percent_done}%" : '—';
        
        $picNames = [];
        $picUsers = $project->pic_users ?? collect();
        foreach ($picUsers as $picUser) {
            if ($picUser && $picUser->employee_name) {
                $picNames[] = $picUser->employee_name;
            }
        }
        $picsText = !empty($picNames) ? implode(', ', $picNames) : '—';

        return "
            <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                <tr><td style='width:140px'><b>Task</b></td><td>Project</td></tr>
                <tr><td><b>Ticket No</b></td><td>" . e($project->project_ticket_no ?? '—') . "</td></tr>
                <tr><td><b>Name</b></td><td>" . e($project->project_name ?? "Project {$project->sk_project}") . "</td></tr>
                <tr><td><b>Status</b></td><td>" . e($project->project_status ?? '—') . "</td></tr>
                <tr><td><b>Lead</b></td><td>" . e($lead) . "</td></tr>
                <tr><td><b>PIC</b></td><td>" . e($picsText) . "</td></tr>
                    <tr><td><b>Start</b></td><td>" . e($project->start_date?->format('M j, Y')) . "</td></tr>
                    <tr><td><b>End</b></td><td>" . e($project->end_date?->format('M j, Y')) . "</td></tr>
                <tr><td><b>% Done</b></td><td>" . e($done) . "</td></tr>
            </table>
        ";
    }

    /**
     * Get MTC details HTML
     */
    private function getMtcDetails($mtc): string
    {
        $createdBy = $mtc->created_by_id ? User::find($mtc->created_by_id) : null;
        $resolver = $mtc->resolver_id ? User::find($mtc->resolver_id) : null;

        return "
            <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                <tr><td style='width:140px'><b>Task</b></td><td>Non-Project</td></tr>
                <tr><td><b>No Ticket</b></td><td>" . e($mtc->no_tiket ?? '—') . "</td></tr>
                <tr><td><b>Application</b></td><td>" . e($mtc->application ?? '—') . "</td></tr>
                <tr><td><b>Type</b></td><td>" . e($mtc->type ?? '—') . "</td></tr>
                <tr><td><b>Date</b></td><td>" . e($mtc->tanggal?->format('M j, Y')) . "</td></tr>
                <tr><td><b>Created By</b></td><td>" . e($createdBy?->employee_name ?? '—') . "</td></tr>
                <tr><td><b>Resolver</b></td><td>" . e($resolver?->employee_name ?? '—') . "</td></tr>
                <tr><td><b>Description</b></td><td>" . nl2br(e($mtc->deskripsi ?? '—')) . "</td></tr>
                <tr><td><b>Solution</b></td><td>" . nl2br(e($mtc->solusi ?? '—')) . "</td></tr>
            </table>
        ";
    }
}
