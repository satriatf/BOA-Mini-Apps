<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'pmo_id',
        'phase_cr',
        'project_name',
        'status',
        'tech_lead',
        'pic_1',
        'pic_2',
        'start_date',
        'end_date',
        'days',
        'percent_done',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    // Clamp percent_done ke 0..100
    protected static function booted(): void
    {
        static::saving(function (Project $p) {
            if ($p->percent_done !== null) {
                $p->percent_done = max(0, min(100, (int) $p->percent_done));
            }
            // TIDAK ada perhitungan days otomatis lagi.
        });
    }
    public function techLead() { return $this->belongsTo(\App\Models\User::class, 'tech_lead'); }
    public function pic1() { return $this->belongsTo(\App\Models\User::class, 'pic_1'); }
    public function pic2() { return $this->belongsTo(\App\Models\User::class, 'pic_2'); }
}
