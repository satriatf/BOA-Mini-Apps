<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Mtcs\MtcResource;
use App\Models\Project;
use App\Models\Mtc;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;

class ProjectTimeline extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Timeline';
    protected static ?int    $navigationSort  = 14;

    protected string $view = 'filament.pages.project-timeline';

    /** Tahun aktif */
    public ?int $year = null;

    public function mount(): void
    {
        $raw = request()->query('year');
        $y   = is_numeric($raw) ? (int) $raw : null;
        if (empty($y) || $y < 1900 || $y > 2100) {
            $y = now()->year;
        }
        $this->year = $y;
    }

    public static function getNavigationLabel(): string { return 'Timeline'; }
    public function getTitle(): string { return 'Project Timeline'; }

    /** Ambil semua event Project + MTC */
    public function getEvents(): array
    {
        $year = $this->year ?? now()->year;
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd   = Carbon::create($year, 12, 31);

        $events = [];

        /* =========================
         * PROJECTS (rentang full, bar menyambung)
         * ========================= */
        foreach (
            Project::query()
                ->select(['id','pmo_id','project_name','status','tech_lead','start_date','end_date','percent_done'])
                ->get() as $p
        ) {
            if (!$p->start_date && !$p->end_date) continue;

            $ps = $p->start_date ? Carbon::parse($p->start_date)->startOfDay() : $yearStart->clone();
            $pe = $p->end_date   ? Carbon::parse($p->end_date)->endOfDay()   : $yearEnd->clone();

            $s = $ps->max($yearStart);
            $e = $pe->min($yearEnd);

            $title = trim(($p->pmo_id ? "{$p->pmo_id} " : '') . ($p->project_name ?? "Project #{$p->id}"));
            $leadUser = $p->tech_lead ? User::find($p->tech_lead) : null;
            $lead     = $leadUser?->name ?? '—';
            $done     = isset($p->percent_done) ? "{$p->percent_done}%" : '—';

            $detailsHtml = "
                <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                    <tr><td style='width:140px'><b>Type</b></td><td>Project</td></tr>
                    <tr><td><b>PMO</b></td><td>" . e($p->pmo_id ?? '—') . "</td></tr>
                    <tr><td><b>Name</b></td><td>" . e($p->project_name ?? "Project #{$p->id}") . "</td></tr>
                    <tr><td><b>Status</b></td><td>" . e($p->status ?? '—') . "</td></tr>
                    <tr><td><b>Lead</b></td><td>" . e($lead) . "</td></tr>
                    <tr><td><b>Start</b></td><td>{$ps->toDateString()}</td></tr>
                    <tr><td><b>End</b></td><td>{$pe->toDateString()}</td></tr>
                    <tr><td><b>% Done</b></td><td>" . e($done) . "</td></tr>
                </table>
            ";

            $events[] = [
                'id'    => "project-{$p->id}",
                'title' => $title,
                'start' => $s->toDateString(),
                'end'   => $e->copy()->addDay()->toDateString(), // end exclusive
                'allDay'=> true,
                'url'   => ProjectResource::getUrl('edit', ['record' => $p]),
                'backgroundColor' => '#3b82f6',
                'textColor'       => '#ffffff',
                'extendedProps'   => ['details' => $detailsHtml],
            ];
        }

        /* =========================
         * MTC / NON-PROJECT (1 hari)
         * ========================= */
        foreach (
            Mtc::query()
                ->whereYear('tanggal', $year)
                ->with(['createdBy:id,name','resolver:id,name'])
                ->get() as $t
        ) {
            $d = Carbon::parse($t->tanggal)->startOfDay();

            $title = trim(($t->no_tiket ? "#{$t->no_tiket} " : '') . ($t->application ?? 'Non-Project'));

            $detailsHtml = "
                <table style='width:100%;border-collapse:collapse' cellpadding='6'>
                    <tr><td style='width:140px'><b>Type</b></td><td>Non-Project</td></tr>
                    <tr><td><b>No Ticket</b></td><td>" . e($t->no_tiket) . "</td></tr>
                    <tr><td><b>Application</b></td><td>" . e($t->application) . "</td></tr>
                    <tr><td><b>Category</b></td><td>" . e($t->type) . "</td></tr>
                    <tr><td><b>Date</b></td><td>{$d->toDateString()}</td></tr>
                    <tr><td><b>Created By</b></td><td>" . e(optional($t->createdBy)->name ?? '—') . "</td></tr>
                    <tr><td><b>Resolver</b></td><td>" . e(optional($t->resolver)->name ?? '—') . "</td></tr>
                    <tr><td><b>Description</b></td><td>" . nl2br(e($t->deskripsi)) . "</td></tr>
                    <tr><td><b>Solution</b></td><td>" . nl2br(e($t->solusi ?? '—')) . "</td></tr>
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
                'extendedProps'   => ['details' => $detailsHtml],
            ];
        }

        return $events;
    }
}
