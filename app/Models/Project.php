<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectPic;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'sk_project';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'sk_project',
        'project_ticket_no',
        'project_name',
        'project_status',
        'application',
        'technical_lead',
        'pics',          // JSON array of user IDs
        'start_date',
        'end_date',
        'total_day',
        'percent_done',
        'deploy_date',
        'is_delete',
        'create_by',
        'create_date',
        'modified_by',
        'modified_date',
        'deleted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'deploy_date' => 'date',
        'pics'       => 'array',   // Laravel decode JSON -> array
        'create_date' => 'datetime',
        'modified_date' => 'datetime',
        'is_delete' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->sk_project)) {
                $project->sk_project = (string) Str::uuid();
            }
            if (Auth::check() && empty($project->create_by)) {
                $project->create_by = Auth::user()->employee_name;
            }
        });

        static::saving(function (Project $p) {
            if ($p->percent_done !== null) {
                $p->percent_done = max(0, min(100, (int) $p->percent_done));
            }
        });
    }

    public function techLead()
    {
        return $this->belongsTo(related: User::class, foreignKey: 'technical_lead', ownerKey: 'sk_user');
    }

    public function projectPics()
    {
        return $this->hasMany(ProjectPic::class, 'sk_project', 'sk_project');
    }
    /**
     * Semua user PIC berdasarkan array ID di kolom `pics`.
     * Selalu return Collection (tidak pernah null) agar aman dipakai di tabel.
     */
    public function getPicUsersAttribute(): Collection
    {
        $pics = $this->projectPics()->with('user')->get();
        if ($pics->isEmpty()) {
            return collect();
        }

        return $pics->map(fn($p) => $p->user)->filter();
    }

    /**
     * (Opsional) Nama-nama PIC dalam bentuk array sederhana.
     */
    public function getPicNamesAttribute(): array
    {
        return $this->pic_users->pluck('employee_name')->all();
    }
}
