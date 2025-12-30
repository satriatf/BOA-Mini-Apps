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

        $activeEmployees = User::where('is_active', 'Active')
            ->where(function ($q) {
                $q->whereNull('is_admin')
                  ->orWhere('is_admin', false)
                  ->orWhere('is_admin', 0);
            })
            ->get()
            ->keyBy('sk_user');

        $activeEmployees = $activeEmployees->filter(function ($emp) {
            $lvlRaw = trim((string) ($emp->level ?? ''));

            $lvlNorm = strtolower(preg_replace('/\s+/', ' ', $lvlRaw));

            // Hide managers
            if (str_contains($lvlNorm, 'manager')) {
                return false;
            }

            // Hide specific employees by name
            $name = strtolower(trim((string) ($emp->employee_name ?? '')));
            if ($name === 'destiana dwi saputri' || $name === 'syafira indah nurkafianti') {
                return false;
            }

            return true;
        });

        $employeeTasks = [];

        // Process Projects - include projects that have project dates OR PIC rows with dates
        $projects = Project::query()
            ->where(fn($q) => $q->whereNotNull('start_date')
                ->orWhereNotNull('end_date')
                ->orWhereHas('projectPics', fn($q2) => $q2->whereNotNull('start_date')->orWhereNotNull('end_date'))
            )
            ->get();

        foreach ($projects as $project) {
            // Determine project-wide start/end. Prefer explicit project dates,
            // but if missing, try to derive a span from PIC rows (min start, max end).
            $start = $project->start_date ? Carbon::parse($project->start_date)->startOfDay() : null;
            $end = $project->end_date ? Carbon::parse($project->end_date)->endOfDay() : null;

            // If project has no dates, but some PIC rows have dates, derive project span
            if (!$start && !$end) {
                $picsForSpan = $project->projectPics()->whereNotNull('start_date')
                    ->orWhereNotNull('end_date')
                    ->get();

                $minStart = null;
                $maxEnd = null;
                foreach ($picsForSpan as $pSpan) {
                    if ($pSpan->start_date) {
                        $s = Carbon::parse($pSpan->start_date)->startOfDay();
                        $minStart = $minStart ? min($minStart, $s) : $s;
                    }
                    if ($pSpan->end_date) {
                        $e = Carbon::parse($pSpan->end_date)->endOfDay();
                        $maxEnd = $maxEnd ? max($maxEnd, $e) : $e;
                    }
                }

                if ($minStart || $maxEnd) {
                    $start = $minStart ?? $maxEnd;
                    $end = $maxEnd ?? $minStart;
                }
            }

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

            if ($project->technical_lead) {
                $employeeId = $project->technical_lead;

                if ($activeEmployees->has($employeeId)) {
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
        }

        // Process Projects - also add PICs as separate tasks using project_pics relationship
        foreach ($projects as $project) {
            // Load PIC rows from the project_pics table (soft-deleted rows are excluded by default)
            $pics = $project->projectPics()->with('user')->orderBy('start_date')->get();
            if ($pics->isEmpty()) continue;

            // Create task title
            $projectName = trim($project->project_name ?? "Project {$project->sk_project}");
            $ticketNo = trim((string) ($project->project_ticket_no ?? ''));
            $title = $ticketNo !== '' ? "{$projectName} [{$ticketNo}]" : "{$projectName} [NO TIKET]";

            // Group PICs by user
            $picsByUser = [];
            foreach ($pics as $pic) {
                if (!$pic->user) continue;
                if (!$activeEmployees->has($pic->user->sk_user)) continue;
                
                $userId = $pic->user->sk_user;
                if (!isset($picsByUser[$userId])) {
                    $picsByUser[$userId] = [];
                }
                $picsByUser[$userId][] = $pic;
            }

            // Process each user's PICs
            foreach ($picsByUser as $employeeId => $userPics) {
                // Sort PICs by date range size (smaller ranges first) to prioritize specific assignments
                usort($userPics, function($a, $b) {
                    $aStart = $a->start_date;
                    $aEnd = $a->end_date ?? $a->start_date;
                    $bStart = $b->start_date;
                    $bEnd = $b->end_date ?? $b->start_date;
                    
                    $aDays = $aStart->diffInDays($aEnd) + 1;
                    $bDays = $bStart->diffInDays($bEnd) + 1;
                    
                    // Smaller range first
                    return $aDays <=> $bDays;
                });

                // Collect all assigned dates from ALL pics for this user
                $allAssignedDates = [];
                foreach ($userPics as $pic) {
                    $picStart = $pic->start_date ? Carbon::parse($pic->start_date)->startOfDay() : null;
                    $picEnd = $pic->end_date ? Carbon::parse($pic->end_date)->endOfDay() : null;
                    
                    if (!$picStart || !$picEnd) {
                        if ($picStart) $picEnd = $picStart->clone();
                        if ($picEnd) $picStart = $picEnd->clone();
                    }
                    
                    if ($picStart && $picEnd) {
                        $current = $picStart->copy();
                        while ($current->lte($picEnd)) {
                            $allAssignedDates[$current->format('Y-m-d')] = true;
                            $current->addDay();
                        }
                    }
                }

                // Now process each PIC and only show dates that haven't been "claimed" by previous PICs
                $claimedDates = [];
                
                foreach ($userPics as $pic) {
                    $picStart = $pic->start_date ? Carbon::parse($pic->start_date)->startOfDay() : null;
                    $picEnd = $pic->end_date ? Carbon::parse($pic->end_date)->endOfDay() : null;

                    if (!$picStart && !$picEnd) continue;
                    if ($picStart && !$picEnd) $picEnd = $picStart->clone();
                    if (!$picStart && $picEnd) $picStart = $picEnd->clone();

                    // Clamp to year
                    $picStartClamped = $picStart->clone()->max($yearStart);
                    $picEndClamped = $picEnd->clone()->min($yearEnd);

                    if ($picStartClamped->gt($yearEnd) || $picEndClamped->lt($yearStart)) continue;

                    // For this PIC, find which dates are NOT yet claimed
                    $availableDates = [];
                    $current = $picStartClamped->copy();
                    while ($current->lte($picEndClamped)) {
                        $dateKey = $current->format('Y-m-d');
                        if (!isset($claimedDates[$dateKey])) {
                            $availableDates[] = $dateKey;
                            $claimedDates[$dateKey] = true;
                        }
                        $current->addDay();
                    }

                    if (empty($availableDates)) continue;

                    // Group consecutive dates
                    $ranges = [];
                    $rangeStart = null;
                    $rangeEnd = null;

                    foreach ($availableDates as $dateStr) {
                        $date = Carbon::parse($dateStr);
                        
                        if ($rangeStart === null) {
                            $rangeStart = $date->copy();
                            $rangeEnd = $date->copy();
                        } else {
                            if ($date->copy()->subDay()->isSameDay($rangeEnd)) {
                                $rangeEnd = $date->copy();
                            } else {
                                $ranges[] = [
                                    'start' => $rangeStart->format('Y-m-d'),
                                    'end' => $rangeEnd->format('Y-m-d')
                                ];
                                $rangeStart = $date->copy();
                                $rangeEnd = $date->copy();
                            }
                        }
                    }

                    if ($rangeStart !== null) {
                        $ranges[] = [
                            'start' => $rangeStart->format('Y-m-d'),
                            'end' => $rangeEnd->format('Y-m-d')
                        ];
                    }

                    // Add tasks
                    if (!isset($employeeTasks[$employeeId])) {
                        $employeeTasks[$employeeId] = [];
                    }

                    foreach ($ranges as $range) {
                        // Check if any PIC has overtime during this range
                        $hasOvertimeInRange = false;
                        foreach ($userPics as $pic) {
                            if ($pic->has_overtime && $pic->overtime_start_date && $pic->overtime_end_date) {
                                $overtimeStart = Carbon::parse($pic->overtime_start_date);
                                $overtimeEnd = Carbon::parse($pic->overtime_end_date);
                                $rangeStart = Carbon::parse($range['start']);
                                $rangeEnd = Carbon::parse($range['end']);

                                // Check if overtime overlaps with this range
                                if ($overtimeStart->lte($rangeEnd) && $overtimeEnd->gte($rangeStart)) {
                                    $hasOvertimeInRange = true;
                                    break;
                                }
                            }
                        }

                        $employeeTasks[$employeeId][] = [
                            'start' => $range['start'],
                            'end' => $range['end'],
                            'type' => 'project',
                            'title' => $title,
                            'role' => 'PIC',
                            'details' => $this->getProjectDetails($project),
                            'has_overtime' => $hasOvertimeInRange,
                        ];
                    }

                    // Add overtime spans as separate tasks so weekends (overtime) also get colored
                    foreach ($userPics as $pic) {
                        if (!$pic->has_overtime || !$pic->overtime_start_date || !$pic->overtime_end_date) {
                            continue;
                        }

                        $otStart = Carbon::parse($pic->overtime_start_date)->startOfDay();
                        $otEnd = Carbon::parse($pic->overtime_end_date)->endOfDay();

                        // Clamp to year
                        $otStartClamped = $otStart->clone()->max($yearStart);
                        $otEndClamped = $otEnd->clone()->min($yearEnd);

                        if ($otStartClamped->gt($yearEnd) || $otEndClamped->lt($yearStart)) {
                            continue;
                        }

                        $employeeTasks[$employeeId][] = [
                            'start' => $otStartClamped->toDateString(),
                            'end' => $otEndClamped->toDateString(),
                            'type' => 'project',
                            'title' => $title,
                            'role' => 'PIC',
                            'details' => $this->getProjectDetails($project),
                            'has_overtime' => true,
                        ];
                    }
                }
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

            // Add resolver if exists and active
            if ($mtc->resolver_id) {
                $employeeId = $mtc->resolver_id;
                if ($activeEmployees->has($employeeId)) {
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
            }

            // Add created_by if exists, different from resolver, and active
            if ($mtc->created_by_id && $mtc->created_by_id !== $mtc->resolver_id) {
                $employeeId = $mtc->created_by_id;
                if ($activeEmployees->has($employeeId)) {
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
        }

        $levelPriority = [
            'asisten manager' => 1,
            'section head' => 2,
            'staff' => 3,
            'intern' => 4,
        ];

        $orderedEmployees = $activeEmployees->sortBy(function ($emp) use ($levelPriority) {
            $name = trim($emp->employee_name ?? '');

            $lvlRaw = trim(strtolower((string) ($emp->level ?? '')));
            $aliases = [
                'asmen' => 'asisten manager',
                'section head' => 'section head',
                'sh' => 'section head',
            ];

            $lvl = $aliases[$lvlRaw] ?? $lvlRaw;
            $prio = $levelPriority[$lvl] ?? 99;

            // Primary sort by priority, secondary by name to match Employees list ordering
            return sprintf('%03d%s', $prio, strtolower($name));
        });

        $rows = [];
        foreach ($orderedEmployees as $employee) {
            $tasks = $employeeTasks[$employee->sk_user] ?? [];

            $rows[] = [
                'name' => $employee->employee_name,
                'tasks' => $tasks,
            ];
        }

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
        $seen = [];
        foreach ($picUsers as $picUser) {
            $name = optional($picUser)->employee_name ?: null;
            $uid = optional($picUser)->sk_user ?: null;
            if ($uid) {
                if (isset($seen[$uid])) continue;
                $seen[$uid] = true;
            } else {
                if ($name === null) continue;
                if (in_array($name, $seen, true)) continue;
                $seen[] = $name;
            }

            if ($name) $picNames[] = $name;
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
