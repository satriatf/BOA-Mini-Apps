<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Project;

class ProjectsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    /**
     * Return a collection of arrays (rows)
     */
    public function collection()
    {
        $out = [];

        foreach ($this->rows as $r) {
            $fmt = function ($d) {
                if (!$d) return null;
                if ($d instanceof \DateTimeInterface) return $d->format('Y-m-d');
                try {
                    return \Carbon\Carbon::parse($d)->toDateString();
                } catch (\Throwable $e) {
                    return (string) $d;
                }
            };

            $techLead = optional($r->techLead)->employee_name ?? $r->technical_lead;

            $out[] = [
                $r->project_ticket_no,
                $r->project_name,
                $r->project_status,
                $techLead,
                null,
                $fmt($r->start_date ?? null),
                $fmt($r->end_date ?? null),
                $r->total_day ?? null,
                $r->percent_done ?? null,
            ];

            $pics = null;
            $proj = null;

            $sk = null;
            if (is_object($r)) {
                $sk = $r->sk_project ?? $r->skProject ?? null;
            } elseif (is_array($r)) {
                $sk = $r['sk_project'] ?? $r['skProject'] ?? null;
            }

            if (! empty($sk)) {
                try {
                    $proj = Project::with('projectPics.user')->find($sk);
                    if ($proj && $proj->projectPics->isNotEmpty()) {
                        // Use DB projectPics as authoritative
                        $pics = $proj->projectPics->all();
                    }
                } catch (\Throwable $e) {
                    $proj = null;
                }
            }

            if (empty($pics)) {
                if (! empty($r->project_pics)) {
                    $pics = $r->project_pics;
                } elseif (! empty($r->pic_rows)) {
                    $pics = $r->pic_rows;
                } elseif (! empty($r->pic_names) && is_array($r->pic_names)) {
                    $pics = array_map(fn($n) => ['employee_name' => $n], $r->pic_names);
                } elseif (! empty($r->pics) && is_array($r->pics)) {
                    $pics = array_map(fn($p) => (is_object($p) ? $p : ['employee_name' => $p]), $r->pics);
                }
            }

            if (! empty($pics) && is_iterable($pics)) {
                $seen = [];
                $unique = [];
                foreach ($pics as $pp) {
                    $uid = null;
                    if (is_object($pp)) {
                        $uid = (string) ($pp->sk_user ?? $pp->user?->sk_user ?? '');
                    } elseif (is_array($pp) || $pp instanceof \ArrayAccess) {
                        if (isset($pp['sk_user'])) $uid = (string) $pp['sk_user'];
                        elseif (isset($pp['user']) && is_array($pp['user']) && isset($pp['user']['sk_user'])) $uid = (string) $pp['user']['sk_user'];
                        elseif (isset($pp['user']) && is_object($pp['user']) && isset($pp['user']->sk_user)) $uid = (string) $pp['user']->sk_user;
                        else $uid = (string) ($pp['employee_name'] ?? $pp['name'] ?? '');
                    } else {
                        $uid = (string) $pp;
                    }

                    if ($uid === '') {
                        $uid = md5(serialize($pp));
                    }

                    if (! in_array($uid, $seen, true)) {
                        $seen[] = $uid;
                        $unique[] = $pp;
                    }
                }

                $pics = $unique;
            }

            if ($proj && ! empty($r->pics) && is_array($r->pics) && ! empty($proj->projectPics)) {
                $ordered = [];
                foreach ($r->pics as $uid) {
                    $found = $proj->projectPics->first(fn($pp) => (string) ($pp->sk_user ?? $pp->user?->sk_user ?? '') === (string) $uid);
                    if ($found) $ordered[] = $found;
                }
                $orderedUserIds = array_map(fn($o) => (string) ($o->sk_user ?? $o->user?->sk_user ?? ''), $ordered);
                foreach ($proj->projectPics as $pp) {
                    $ppUid = (string) ($pp->sk_user ?? $pp->user?->sk_user ?? '');
                    if ($ppUid === '') continue;
                    if (! in_array($ppUid, $orderedUserIds, true)) {
                        $ordered[] = $pp;
                        $orderedUserIds[] = $ppUid;
                    }
                }
                $pics = $ordered;
            }

            if (! empty($pics)) {
                foreach ($pics as $i => $p) {
                    $picName = null;
                    $picStart = null;
                    $picEnd = null;

                    if (is_array($p) || $p instanceof \ArrayAccess) {
                        $picName = $p['employee_name'] ?? $p['name'] ?? null;
                        $picStart = $p['start_date'] ?? $p['from'] ?? null;
                        $picEnd = $p['end_date'] ?? $p['to'] ?? null;
                        if (isset($p['user']) && is_object($p['user'])) {
                            $picName = $p['user']->employee_name ?? $picName;
                        }
                    } elseif (is_object($p)) {
                        $picName = $p->user?->employee_name ?? ($p->employee_name ?? $p->name ?? null);
                        $picStart = $p->start_date ?? null;
                        $picEnd = $p->end_date ?? null;
                    } else {
                        $picName = (string) $p;
                    }

                    $out[] = [
                        null,
                        null,
                        null,
                        null,
                        trim((string) (($i + 1) . '. ' . ($picName ?? '—'))),
                        $fmt($picStart) ?? null,
                        $fmt($picEnd) ?? null,
                        null,
                        null,
                    ];
                }
            }
        }

        return collect($out); 
    }

    public function headings(): array
    {
        return ['Project Ticket No', 'Project Name', 'Project Status', 'Technical Lead', 'PIC', 'Start Date', 'End Date', 'Total Days', '% Done'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        try {
            $sheet->getStyle('E')->getAlignment()->setWrapText(true);
        } catch (\Throwable $e) {
            try {
                $sheet->getStyle('E:E')->getAlignment()->setWrapText(true);
            } catch (\Throwable $e) {
            }
        }

        return [];
    }
}
