<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
        return $this->rows->map(function ($r) {
            return [
                $r->project_ticket_no,
                $r->project_name,
                $r->project_status,
                optional($r->techLead)->employee_name ?? $r->technical_lead,
                (function ($r) {
                    $picNames = [];
                    if (! empty($r->pic_names) && is_array($r->pic_names)) {
                        foreach ($r->pic_names as $i => $name) {
                            $picNames[] = ($i + 1) . '. ' . $name;
                        }
                    } elseif (! empty($r->pics) && is_array($r->pics)) {
                        foreach ($r->pics as $i => $id) {
                            $picNames[] = ($i + 1) . '. ' . $id;
                        }
                    }

                    return empty($picNames) ? null : implode("\n", $picNames);
                })($r),
                $r->start_date ? $r->start_date->toDateString() : null,
                $r->end_date ? $r->end_date->toDateString() : null,
                $r->total_day,
                $r->percent_done,
            ];
        })->values();
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
