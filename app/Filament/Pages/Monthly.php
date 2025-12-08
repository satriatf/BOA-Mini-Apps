<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Mtcs\MtcResource;
use App\Filament\Resources\OnLeaves\OnLeaveResource;
use App\Models\Project;
use App\Models\Mtc;
use App\Models\User;
use App\Models\OnLeave;
use App\Models\Holiday;
use Carbon\Carbon;
use Filament\Pages\Page;

class Monthly extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar';
    protected static string|\UnitEnum|null   $navigationGroup = 'Calendar';
    protected static ?int                    $navigationSort  = 1;

    protected static ?string $slug = 'monthly';
    protected string $view = 'filament.pages.monthly';

    public ?int $year = null;

    public static function getNavigationLabel(): string { return 'Monthly'; }
    public function getTitle(): string { return static::getNavigationLabel(); }

    public function mount(): void
    {
        $raw = request()->query('year');
        $y   = is_numeric($raw) ? (int) $raw : null;
        $this->year = (! $y || $y < 1900 || $y > 2100) ? now()->year : $y;
    }

    public function getEvents(): array
    {
        $year = $this->year ?? now()->year;
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd   = Carbon::create($year, 12, 31);

        $activeEmployees = User::where('is_active', 'Active')
            ->where(function ($q) {
                $q->whereNull('is_admin')
                  ->orWhere('is_admin', false)
                  ->orWhere('is_admin', 0);
            })
            ->get()
            ->keyBy('sk_user');

        $events = [];

        // ---------------- PROJECTS ----------------
        foreach (
            Project::query()
                ->select(['sk_project','project_ticket_no','project_name','project_status','technical_lead','pics','start_date','end_date','percent_done'])
                ->get() as $p
        ) {
            if (!$p->start_date && !$p->end_date) continue;

            $ps = $p->start_date ? Carbon::parse($p->start_date)->startOfDay() : $yearStart->clone();
            $pe = $p->end_date   ? Carbon::parse($p->end_date)->endOfDay()   : $yearEnd->clone();

            // use the full start/end from the record so events span correctly across year boundaries
            $s = $ps;
            $e = $pe;

            // Judul: Project Name [TIKET] / [NO TIKET] (tanpa #)
            $name   = trim($p->project_name ?? "Project {$p->sk_project}");
            $ticket = trim((string) ($p->project_ticket_no ?? ''));
            $title  = $ticket !== '' ? "{$name} [{$ticket}]" : "{$name} [NO TIKET]";

            $leadUser = $p->technical_lead ? ($activeEmployees->get($p->technical_lead) ?? null) : null;
            $lead     = $leadUser?->employee_name ?? '—';

            $hasActiveParticipant = false;
            if ($leadUser) $hasActiveParticipant = true;
            if (!$hasActiveParticipant && is_array($p->pics) && !empty($p->pics)) {
                foreach ($p->pics as $picId) {
                    if ($activeEmployees->has($picId)) { $hasActiveParticipant = true; break; }
                }
            }

            if (! $hasActiveParticipant) continue;
            $done     = isset($p->percent_done) ? "{$p->percent_done}%" : '—';
            
            // Get PIC names
            $picNames = [];
            if (is_array($p->pics) && !empty($p->pics)) {
                $picUsers = User::whereIn('sk_user', $p->pics)
                    ->where('is_active', 'Active')
                    ->get();
                $picNames = $picUsers->pluck('employee_name')->toArray();
            }
            $pics = !empty($picNames) ? implode(', ', $picNames) : '—';

                $detailsHtml = "
                    <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                        <tr><td style='width:140px'><b>Task</b></td><td>Project</td></tr>
                        <tr><td><b>Ticket No</b></td><td>" . e($p->project_ticket_no ?? '—') . "</td></tr>
                        <tr><td><b>Name</b></td><td>" . e($p->project_name ?? "Project {$p->sk_project}") . "</td></tr>
                        <tr><td><b>Status</b></td><td>" . e($p->project_status ?? '—') . "</td></tr>
                        <tr><td><b>Lead</b></td><td>" . e($lead) . "</td></tr>
                        <tr><td><b>PIC</b></td><td>" . e($pics) . "</td></tr>
                        <tr><td><b>Start</b></td><td>" . e($ps?->format('M j, Y')) . "</td></tr>
                        <tr><td><b>End</b></td><td>" . e($pe?->format('M j, Y')) . "</td></tr>
                        <tr><td><b>% Done</b></td><td>" . e($done) . "</td></tr>
                    </table>
                ";
            $events[] = [
                'id'    => "project-{$p->sk_project}",
                'title' => $title,
                'start' => $s->toDateString(),
                'end'   => $e->copy()->addDay()->toDateString(), // end exclusive
                'allDay'=> true,
                'url'   => ProjectResource::getUrl('edit', ['record' => $p]),
                'backgroundColor' => '#3b82f6',
                'textColor'       => '#ffffff',
                'extendedProps'   => ['type' => 'project', 'details' => $detailsHtml],
            ];
        }

        // ---------------- MTC (NON-PROJECT) ----------------
        foreach (
            Mtc::query()
                ->whereYear('tanggal', $year)
                ->with(['createdBy:sk_user,employee_name','resolver:sk_user,employee_name'])
                ->get() as $t
        ) {
            if (!$t->tanggal) continue;
            $d = Carbon::parse($t->tanggal)->startOfDay();

            // Only include MTC events that involve an active non-admin user (resolver or created_by)
            $hasActive = false;
            if ($t->resolver_id && $activeEmployees->has($t->resolver_id)) $hasActive = true;
            if (! $hasActive && $t->created_by_id && $activeEmployees->has($t->created_by_id)) $hasActive = true;
            if (! $hasActive) continue;
            $app   = trim($t->application ?? 'Non-Project');
            $no    = trim((string) ($t->no_tiket ?? ''));
            $title = $no !== '' ? "{$app} [{$no}]" : "{$app} [NO TIKET]";

            $detailsHtml = "
                <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                    <tr><td style='width:140px'><b>Task</b></td><td>Non-Project</td></tr>
                    <tr><td><b>Ticket No</b></td><td>" . e($t->no_tiket ?? '—') . "</td></tr>
                    <tr><td><b>Name</b></td><td>" . e($t->application ?? '—') . "</td></tr>
                    <tr><td><b>Status</b></td><td>" . e($t->type ?? '—') . "</td></tr>
                    <tr><td><b>Lead</b></td><td>—</td></tr>
                    <tr><td><b>PIC</b></td><td>—</td></tr>
                    <tr><td><b>Start</b></td><td>" . e($d?->format('M j, Y')) . "</td></tr>
                    <tr><td><b>End</b></td><td>" . e($d?->format('M j, Y')) . "</td></tr>
                    <tr><td><b>% Done</b></td><td>—</td></tr>
                </table>
            ";

            $events[] = [
                'id'    => "mtc-{$t->id}",
                'title' => $title,
                'start' => $d->toDateString(),
                'end'   => $d->copy()->addDay()->toDateString(),
                'allDay'=> true,
                'url'   => MtcResource::getUrl('edit', ['record' => $t]),
                'backgroundColor' => '#f59e0b',
                'textColor'       => '#1f2937',
                'extendedProps'   => ['type' => 'mtc', 'details' => $detailsHtml],
            ];
        }

        // ---------------- ON LEAVES ----------------
        foreach (
            OnLeave::query()
                ->select(['id','user_id','leave_type','start_date','end_date'])
                ->get() as $o
        ) {
            if (! $o->start_date && ! $o->end_date) continue;

            $ps = $o->start_date ? Carbon::parse($o->start_date)->startOfDay() : $yearStart->clone();
            $pe = $o->end_date   ? Carbon::parse($o->end_date)->endOfDay()   : $yearEnd->clone();

            $s = $ps->max($yearStart);
            $e = $pe->min($yearEnd);

            // Only include if user is active
            $user = $activeEmployees->get($o->user_id) ?? $o->user;
            if (! $user) continue;

            $title = ($user->employee_name ?? ('User #'.$o->user_id)) . ' — ' . ($o->leave_type ?? 'Leave');

            $detailsHtml = "
                <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                    <tr><td style='width:140px'><b>Task</b></td><td>On Leave</td></tr>
                    <tr><td><b>User</b></td><td>" . e($user->employee_name ?? '—') . "</td></tr>
                    <tr><td><b>Type</b></td><td>" . e($o->leave_type ?? '—') . "</td></tr>
                    <tr><td><b>Start</b></td><td>" . e($ps?->format('M j, Y')) . "</td></tr>
                    <tr><td><b>End</b></td><td>" . e($pe?->format('M j, Y')) . "</td></tr>
                </table>
            ";

            $events[] = [
                'id'    => "onleave-{$o->id}",
                'title' => $title,
                'start' => $ps->toDateString(),
                'end'   => $pe->copy()->addDay()->toDateString(),
                'allDay'=> true,
                'display' => 'block',
                'url'   => OnLeaveResource::getUrl('edit', ['record' => $o]),
                'backgroundColor' => '#ef4444',
                'textColor'       => '#ffffff',
                'extendedProps'   => ['type' => 'onleave', 'details' => $detailsHtml],
            ];
        }

        // ---------------- HOLIDAYS ----------------
        foreach (
            Holiday::query()
                ->whereYear('date', $year)
                ->get() as $h
        ) {
            if (! $h->date) continue;
            $d = Carbon::parse($h->date)->startOfDay();
            $title = 'Holiday';
            $desc  = trim($h->desc ?? '');
            if ($desc !== '') {
                $title .= " — {$desc}";
            }

            $detailsHtml = "
                <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                    <tr><td style='width:140px'><b>Type</b></td><td>Holiday</td></tr>
                    <tr><td><b>Date</b></td><td>" . e($d->format('M j, Y')) . "</td></tr>
                    <tr><td><b>Description</b></td><td>" . e($h->desc ?? '—') . "</td></tr>
                </table>
            ";

            $events[] = [
                'id'    => "holiday-{$h->id}",
                'title' => '', // hide text on calendar
                'start' => $d->toDateString(),
                'end'   => $d->copy()->addDay()->toDateString(),
                'allDay'=> true,
                'display' => 'background',
                'backgroundColor' => '#00ff00',
                'borderColor'     => '#00ff00',
                'extendedProps'   => [
                    'type' => 'holiday',
                    'details' => $detailsHtml,
                    'detailTitle' => $title,
                ],
            ];
        }

        return $events;
    }
}
