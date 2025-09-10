<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'pmo_id',
        'phase_cr',
        'project_name',
        'status',
        'tech_lead',
        'pics',          // JSON array of user IDs
        'start_date',
        'end_date',
        'days',
        'percent_done',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'pics'       => 'array',   // Laravel decode JSON -> array
    ];

    protected static function booted(): void
    {
        static::saving(function (Project $p) {
            if ($p->percent_done !== null) {
                $p->percent_done = max(0, min(100, (int) $p->percent_done));
            }
        });
    }

    public function techLead()
    {
        return $this->belongsTo(User::class, 'tech_lead');
    }

    /**
     * Semua user PIC berdasarkan array ID di kolom `pics`.
     * Selalu return Collection (tidak pernah null) agar aman dipakai di tabel.
     */
    public function getPicUsersAttribute(): Collection
    {
        $ids = is_array($this->pics) ? $this->pics : [];
        if (empty($ids)) {
            return collect();
        }

        return User::whereIn('id', $ids)->get();
    }

    /**
     * (Opsional) Nama-nama PIC dalam bentuk array sederhana.
     */
    public function getPicNamesAttribute(): array
    {
        return $this->pic_users->pluck('name')->all();
    }
}
