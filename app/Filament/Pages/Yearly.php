<?php

namespace App\Filament\Pages;

use App\Models\Mtc;
use App\Models\Project;
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

            // Judul: Project Name [TIKET] / [NO TIKET]
            $name   = trim($p->project_name ?? "Project {$p->sk_project}");
            $ticket = trim((string) ($p->project_ticket_no ?? ''));
            $title  = $ticket !== '' ? "{$name} [{$ticket}]" : "{$name} [NO TIKET]";

            $lead = $p->techLead->employee_name ?? '—';
            $done = isset($p->percent_done) ? "{$p->percent_done}%" : '—';
            
            // Get PIC names
            $picNames = [];
            if (is_array($p->pics) && !empty($p->pics)) {
                $picUsers = User::whereIn('sk_user', $p->pics)->get();
                $picNames = $picUsers->pluck('employee_name')->toArray();
            }
            $pics = !empty($picNames) ? implode(', ', $picNames) : '—';

            // HTML detail (sama seperti Monthly)
            $detailsHtml = "
                <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                    <tr><td style='width:140px'><b>Type</b></td><td>Project</td></tr>
                    <tr><td><b>Ticket No</b></td><td>" . e($p->project_ticket_no ?? '—') . "</td></tr>
                    <tr><td><b>Name</b></td><td>" . e($p->project_name ?? '—') . "</td></tr>
                    <tr><td><b>Status</b></td><td>" . e($p->project_status ?? '—') . "</td></tr>
                    <tr><td><b>Lead</b></td><td>" . e($lead) . "</td></tr>
                    <tr><td><b>PIC</b></td><td>" . e($pics) . "</td></tr>
                    <tr><td><b>Start</b></td><td>" . e($start?->toDateString()) . "</td></tr>
                    <tr><td><b>End</b></td><td>" . e($end?->toDateString()) . "</td></tr>
                    <tr><td><b>% Done</b></td><td>" . e($done) . "</td></tr>
                </table>
            ";

            $events[] = [
                'title'   => $title,
                'start'   => $s->toDateString(),
                'end'     => $e->clone()->addDay()->toDateString(), // end exclusive utk FC
                'allDay'  => true,
                'display' => 'block',
                'extendedProps' => [
                    'type'    => 'project',
                    'details' => $detailsHtml,
                ],
            ];
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

            // Judul: Application [TIKET] / [NO TIKET]
            $app   = trim($t->application ?? 'Non-Project');
            $no    = trim((string) ($t->no_tiket ?? ''));
            $title = $no !== '' ? "{$app} [{$no}]" : "{$app} [NO TIKET]";

            $detailsHtml = "
                <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                    <tr><td style='width:140px'><b>Type</b></td><td>Non-Project</td></tr>
                    <tr><td><b>No Ticket</b></td><td>" . e($t->no_tiket ?? '—') . "</td></tr>
                    <tr><td><b>Application</b></td><td>" . e($t->application ?? '—') . "</td></tr>
                    <tr><td><b>Category</b></td><td>" . e($t->type ?? '—') . "</td></tr>
                    <tr><td><b>Date</b></td><td>" . e($d->toDateString()) . "</td></tr>
                    <tr><td><b>Created By</b></td><td>" . e(optional($t->createdBy)->employee_name ?? '—') . "</td></tr>
                    <tr><td><b>Resolver</b></td><td>" . e(optional($t->resolver)->employee_name ?? '—') . "</td></tr>
                    <tr><td><b>Description</b></td><td>" . nl2br(e($t->deskripsi ?? '—')) . "</td></tr>
                    <tr><td><b>Solution</b></td><td>" . nl2br(e($t->solusi ?? '—')) . "</td></tr>
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

        return $events;
    }
}
