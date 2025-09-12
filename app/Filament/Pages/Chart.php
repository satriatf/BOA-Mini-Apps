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

    // Ikon nav → BackedEnum|string|null
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    // Muncul DI LUAR grouping (bukan di bawah “Tasks” dsb)
    protected static string|\UnitEnum|null $navigationGroup = null;

    // Atur posisi di sidebar (sesuaikan selera)
    protected static ?int $navigationSort = 15;

    /** Filter tahun */
    public int $year;

    /** Event untuk FullCalendar */
    public array $events = [];

    // Label & title via method (lebih aman lintas versi)
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
        $this->year = (int) request()->query('year', now()->year);
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
        // PROJECTS (cek overlap di PHP → paling aman)
        // ======================
        $projects = Project::query()
            ->where(function ($q) {
                $q->whereNotNull('start_date')
                    ->orWhereNotNull('end_date');
            })
            ->with(['techLead']) // hindari N+1 untuk tooltip
            ->orderBy('start_date')
            ->get();

        foreach ($projects as $p) {
            $start = $p->start_date ? \Carbon\Carbon::parse($p->start_date)->startOfDay() : null;
            $end   = $p->end_date   ? \Carbon\Carbon::parse($p->end_date)->endOfDay()   : null;

            if (!$start && !$end) {
                continue; // tidak ada tanggal sama sekali
            }

            // Jika salah satu null → anggap single-day (untuk kalender saja)
            if ($start && !$end) {
                $end = $start->clone();
            }
            if (!$start && $end) {
                $start = $end->clone();
            }

            // Cek overlap dengan tahun yang dipilih
            $overlaps = $end->gte($startOfYear) && $start->lte($endOfYear);
            if (! $overlaps) {
                continue;
            }

            // Clamp ke batas tahun agar event tidak keluar range kalender
            $startClamped = $start->clone()->max($startOfYear);
            $endClamped   = $end->clone()->min($endOfYear);

            // ✅ Days ambil dari form/DB saja
            $daysFromForm = $p->days; // biarkan apa adanya (0, null, dsb)

            $detail = implode("\n", array_filter([
                'Project: '   . ($p->project_name ?? '—'),
                'Status: '    . ($p->status ?? '—'),
                'Tech Lead: ' . ($p->techLead->name ?? '—'),
                'Start: '     . $start?->toDateString(),
                'End: '       . $end?->toDateString(),
                // Tampilkan hanya jika ada/is_numeric
                is_numeric($daysFromForm) ? ('Days: ' . $daysFromForm) : null,
                isset($p->percent_done) ? ('% Done: ' . $p->percent_done . '%') : null,
            ]));

            $url = \App\Filament\Resources\Projects\ProjectResource::getUrl('edit', ['record' => $p]);

            $events[] = [
                'title'   => (string) ($p->project_name ?? ('Project #' . $p->id)),
                'start'   => $startClamped->toDateString(),
                'end'     => $endClamped->clone()->addDay()->toDateString(), // FullCalendar end exclusive
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
                'Ticket: '     . ($t->no_tiket ?? '—'),
                'Type: '       . ($t->type ?? '—'),
                'Application: ' . ($t->application ?? '—'),
                'Date: '       . $date,
                'Created By: ' . ($t->createdBy->name ?? '—'),
                'Resolver: '   . ($t->resolver->name ?? '—'),
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
