<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Mtcs\MtcResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Mtc;
use App\Models\Project;
use Carbon\Carbon;
use Filament\Pages\Page;

class Chart extends Page
{
    // v4: non-static untuk $view
    protected string $view = 'filament.pages.chart';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    protected static string|\UnitEnum|null $navigationGroup = null;
    protected static ?int $navigationSort = 15;

    /** Filter tahun */
    public int $year;

    /** Event untuk FullCalendar */
    public array $events = [];

    public static function getNavigationLabel(): string
    {
        return 'Chart';
    }

    public function getTitle(): string
    {
        return 'Chart';
    }

    public function mount(): void
    {
        // ✅ validasi year supaya tidak 0000
        $raw = request()->query('year');
        $y   = is_numeric($raw) ? (int) $raw : null;
        if (empty($y) || $y < 1900 || $y > 2100) {
            $y = now()->year;
        }

        $this->year   = $y;
        $this->events = $this->buildEvents($this->year);
    }

    /**
     * Build events (projects + mtcs) utk tahun tertentu.
     * - Projects → rentang start_date..end_date
     * - MTC      → 1 hari (tanggal)
     */
    protected function buildEvents(int $year): array
    {
        $events = [];

        $startOfYear = Carbon::create($year, 1, 1)->startOfDay();
        $endOfYear   = Carbon::create($year, 12, 31)->endOfDay();

        // ======================
        // PROJECTS (cek overlap)
        // ======================
        $projects = Project::query()
            ->where(function ($q) {
                $q->whereNotNull('start_date')
                  ->orWhereNotNull('end_date');
            })
            ->with(['techLead'])
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

            $startClamped = $start->clone()->max($startOfYear);
            $endClamped   = $end->clone()->min($endOfYear);

            $daysFromForm = $p->days;

            $detail = implode("\n", array_filter([
                'Project: '   . ($p->project_name ?? '—'),
                'Status: '    . ($p->project_status ?? '—'),
                'Tech Lead: ' . ($p->techLead->employee_name ?? '—'),
                'Start: '     . $start?->toDateString(),
                'End: '       . $end?->toDateString(),
                is_numeric($daysFromForm) ? ('Days: ' . $daysFromForm) : null,
                isset($p->percent_done) ? ('% Done: ' . $p->percent_done . '%') : null,
            ]));

            $url = ProjectResource::getUrl('edit', ['record' => $p]);

            $events[] = [
                'title'   => (string) ($p->project_name ?? ('Project #' . $p->sk_project)),
                'start'   => $startClamped->toDateString(),
                'end'     => $endClamped->clone()->addDay()->toDateString(), // end exclusive
                'allDay'  => true,
                'display' => 'block',
                'extendedProps' => [
                    'type'    => 'project',
                    'details' => $detail,
                    'url'     => $url,
                ],
            ];
        }

        // ======================
        // NON-PROJECTS / MTC (1 hari)
        // ======================
        $mtcs = Mtc::query()
            ->whereYear('tanggal', $year)
            ->with(['resolver', 'createdBy'])
            ->orderBy('tanggal')
            ->get();

        foreach ($mtcs as $t) {
            if (!$t->tanggal) continue;

            $date = Carbon::parse($t->tanggal)->toDateString();

            $title = trim(implode(' – ', array_filter([
                $t->no_tiket ? '#' . $t->no_tiket : null,
                $t->application ?? null,
                $t->type ?? null,
            ])));

            $detail = implode("\n", array_filter([
                'Ticket: '      . ($t->no_tiket ?? '—'),
                'Type: '        . ($t->type ?? '—'),
                'Application: ' . ($t->application ?? '—'),
                'Date: '        . $date,
                'Created By: '  . ($t->createdBy->employee_name ?? '—'),
                'Resolver: '    . ($t->resolver->employee_name ?? '—'),
                'Description: ' . ($t->deskripsi ?? '—'),
                'Solution: '    . ($t->solusi ?? '—'),
            ]));

            $url = MtcResource::getUrl('edit', ['record' => $t]);

            $events[] = [
                'title'   => $title ?: 'Non-Project',
                'start'   => $date,
                'end'     => Carbon::parse($date)->addDay()->toDateString(), // exclusive
                'allDay'  => true,
                'display' => 'block',
                'extendedProps' => [
                    'type'    => 'mtc',
                    'details' => $detail,
                    'url'     => $url,
                ],
            ];
        }

        return $events;
    }
}