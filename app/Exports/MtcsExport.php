<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MtcsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows->map(function ($r) {
            return [
                optional($r->createdBy)->employee_name ?? $r->create_by ?? null,
                $r->no_tiket,
                $r->deskripsi,
                $r->type,
                optional($r->resolver)->employee_name ?? null,
                $r->solusi ?? null,
                $r->application,
                $r->tanggal ? $r->tanggal->toDateString() : null,
                (string) ((int) ($r->attachments_count ?? (is_array($r->attachments) ? count($r->attachments) : 0))),
            ];
        })->values();
    }

    public function headings(): array
    {
        return ['Created By', 'No. Ticket', 'Description', 'Type', 'Resolver PIC', 'Solution', 'Application', 'Date', 'Attachments'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->freezePane('A2');
        try {
            $sheet->getStyle('C')->getAlignment()->setWrapText(true);
        } catch (\Throwable $e) {
            try {
                $sheet->getStyle('C:C')->getAlignment()->setWrapText(true);
            } catch (\Throwable $e) {
            }
        }

        return [];
    }
}
