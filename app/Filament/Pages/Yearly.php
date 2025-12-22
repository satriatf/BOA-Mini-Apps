<?php

namespace App\Filament\Pages;

use App\Models\Mtc;
use App\Models\Project;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;

class Yearly extends Page
{
    protected string $view = 'filament.pages.yearly';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static string|\UnitEnum|null   $navigationGroup = 'Calendar';
    protected static ?int                    $navigationSort  = 2;

    protected static ?string $slug = 'yearly';

    public int $year;
    public array $events = [];

    public static function getNavigationLabel(): string { return 'Yearly'; }
    public function getTitle(): string { return static::getNavigationLabel(); }

    public function mount(): void
    {
        $raw = request()->query('year');
        $y   = is_numeric($raw) ? (int) $raw : null;
        if (empty($y) || $y < 1900 || $y > 2100) $y = now()->year;

        $this->year   = $y;
        $this->events = $this->buildEvents($this->year);
    }

    protected function buildEvents(int $year): array
    {
        $events = [];
        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
        $endOfYear   = Carbon::create($year, 12, 31)->endOfDay();

        $activeEmployees = User::where('is_active', 'Active')
            ->where(function ($q) {
                $q->whereNull('is_admin')
                  ->orWhere('is_admin', false)
                  ->orWhere('is_admin', 0);
            })
            ->get()
            ->keyBy('sk_user');

        /* =========================
         * PROJECTS (rentang, clamp ke tahun)
         * ========================= */
        $projects = Project::query()
            ->where(fn($q) => $q->whereNotNull('start_date')->orWhereNotNull('end_date'))
            ->with(['techLead']) // untuk Lead
            ->orderBy('start_date')
            ->get();

        foreach ($projects as $p) {
            $start = $p->start_date ? Carbon::parse($p->start_date)->startOfDay() : null;
            $end   = $p->end_date   ? Carbon::parse($p->end_date)->endOfDay()   : null;
            if (!$start && !$end) continue;

            if ($start && !$end) $end = $start->clone();
            if (!$start && $end) $start = $end->clone();

            $overlaps = $end->gte($startOfYear) && $start->lte($endOfYear);
            if (!$overlaps) continue;

            $s = $start->clone()->max($startOfYear);
            $e = $end->clone()->min($endOfYear);

            // Bagi rentang menjadi beberapa segmen HANYA hari kerja (Mon–Fri),
            // sehingga event tidak tampil di weekend.
            $segments = [];
            $currentStart = null;
            $currentEnd   = null;

            for ($day = $s->copy(); $day->lte($e); $day->addDay()) {
                // Carbon: 1 = Mon, ..., 6 = Sat, 7 = Sun
                if (in_array($day->dayOfWeekIso, [6, 7], true)) {
                    if ($currentStart) {
                        $segments[] = [$currentStart->copy(), $currentEnd->copy()];
                        $currentStart = $currentEnd = null;
                    }
                    continue;
                }

                if (! $currentStart) {
                    $currentStart = $day->copy();
                }
                $currentEnd = $day->copy();
            }

            if ($currentStart) {
                $segments[] = [$currentStart->copy(), $currentEnd->copy()];
            }

            // Jika seluruh rentang hanya weekend (tidak ada segmen hari kerja), skip.
            if (empty($segments)) {
                continue;
            }

            // Judul: Project Name [TIKET] / [NO TIKET]
            $name   = trim($p->project_name ?? "Project {$p->sk_project}");
            $ticket = trim((string) ($p->project_ticket_no ?? ''));
            $title  = $ticket !== '' ? "{$name} [{$ticket}]" : "{$name} [NO TIKET]";

            $leadUser = $p->techLead ? ($activeEmployees->get($p->techLead->sk_user ?? $p->techLead) ?? null) : null;
            $lead = $leadUser?->employee_name ?? '—';
            $done = isset($p->percent_done) ? "{$p->percent_done}%" : '—';

            $hasActiveParticipant = false;
            if ($leadUser) $hasActiveParticipant = true;
            if (!$hasActiveParticipant && is_array($p->pics) && !empty($p->pics)) {
                foreach ($p->pics as $picId) {
                    if ($activeEmployees->has($picId)) { $hasActiveParticipant = true; break; }
                }
            }

            if (! $hasActiveParticipant) continue;
            $picNames = [];
            if (is_array($p->pics) && !empty($p->pics)) {
                $picUsers = User::whereIn('sk_user', $p->pics)
                    ->where('is_active', 'Active')
                    ->get();
                $picNames = $picUsers->pluck('employee_name')->toArray();
            }
            $pics = !empty($picNames) ? implode(', ', $picNames) : '—';

            // HTML detail (match Project Timeline)
            $detailsHtml = "
                <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                    <tr><td style='width:140px'><b>Task</b></td><td>Project</td></tr>
                    <tr><td><b>Ticket No</b></td><td>" . e($p->project_ticket_no ?? '—') . "</td></tr>
                    <tr><td><b>Name</b></td><td>" . e($p->project_name ?? "Project {$p->sk_project}") . "</td></tr>
                    <tr><td><b>Status</b></td><td>" . e($p->project_status ?? '—') . "</td></tr>
                    <tr><td><b>Lead</b></td><td>" . e($lead) . "</td></tr>
                    <tr><td><b>PIC</b></td><td>" . e($pics) . "</td></tr>
                    <tr><td><b>Start</b></td><td>" . e($start?->format('M j, Y')) . "</td></tr>
                    <tr><td><b>End</b></td><td>" . e($end?->format('M j, Y')) . "</td></tr>
                    <tr><td><b>% Done</b></td><td>" . e($done) . "</td></tr>
                </table>
            ";

            foreach ($segments as [$segStart, $segEnd]) {
                $events[] = [
                    'title'   => $title,
                    'start'   => $segStart->toDateString(),
                    // FullCalendar pakai end exclusive, jadi +1 hari
                    'end'     => $segEnd->clone()->addDay()->toDateString(),
                    'allDay'  => true,
                    'display' => 'block',
                    'extendedProps' => [
                        'type'    => 'project',
                        'details' => $detailsHtml,
                    ],
                ];
            }
        }

        /* =========================
         * MTC / NON-PROJECT (1 hari)
         * ========================= */
        $mtcs = Mtc::query()
            ->whereYear('tanggal', $year)
            ->with(['createdBy:sk_user,employee_name','resolver:sk_user,employee_name'])
            ->orderBy('tanggal')
            ->get();

        foreach ($mtcs as $t) {
            if (!$t->tanggal) continue;

            $d = Carbon::parse($t->tanggal)->startOfDay();

            // Skip jika jatuh di weekend (Sabtu/Minggu)
            if ($d->isWeekend()) {
                continue;
            }

            $hasActive = false;
            if ($t->resolver_id && $activeEmployees->has($t->resolver_id)) $hasActive = true;
            if (! $hasActive && $t->created_by_id && $activeEmployees->has($t->created_by_id)) $hasActive = true;
            if (! $hasActive) continue;

            // Judul: Application [TIKET] / [NO TIKET]
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
                    <tr><td><b>Start</b></td><td>" . e($d->format('M j, Y')) . "</td></tr>
                    <tr><td><b>End</b></td><td>" . e($d->format('M j, Y')) . "</td></tr>
                    <tr><td><b>% Done</b></td><td>—</td></tr>
                </table>
            ";

            $events[] = [
                'title'   => $title,
                'start'   => $d->toDateString(),
                'end'     => $d->copy()->addDay()->toDateString(),
                'allDay'  => true,
                'display' => 'block',
                'extendedProps' => [
                    'type'    => 'mtc',
                    'details' => $detailsHtml,
                ],
            ];
        }

        /* =========================
         * HOLIDAYS (1 hari)
         * ========================= */
        $holidays = Holiday::query()
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();

        foreach ($holidays as $h) {
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
                'title'   => '', // hide text on calendar
                'start'   => $d->toDateString(),
                'end'     => $d->copy()->addDay()->toDateString(),
                'allDay'  => true,
                'display' => 'background',
                'backgroundColor' => '#00ff00',
                'borderColor'     => '#00ff00',
                'extendedProps' => [
                    'type'        => 'holiday',
                    'details'     => $detailsHtml,
                    'detailTitle' => $title,
                ],
            ];
        }

        return $events;
    }
}
