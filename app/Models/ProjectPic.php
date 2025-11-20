<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectPic extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'project_pics';

    protected $fillable = [
        'sk_project',
        'sk_user',
        'start_date',
        'end_date',
        'created_by',
        'deleted_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'sk_project', 'sk_project');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'sk_user', 'sk_user');
    }

    protected static function booted(): void
    {
        static::created(function (ProjectPic $pic) {
            $pic->syncProjectPics();
        });

        static::deleted(function (ProjectPic $pic) {
            $pic->syncProjectPics();
        });

        static::restored(function (ProjectPic $pic) {
            $pic->syncProjectPics();
        });

        static::updated(function (ProjectPic $pic) {
            $pic->syncProjectPics();
        });
    }

    /**
     * Sync the parent project's `pics` JSON column with current PIC rows.
     */
    public function syncProjectPics(): void
    {
        if (empty($this->sk_project)) {
            return;
        }

        $ids = self::where('sk_project', $this->sk_project)
            ->whereNull('deleted_at')
            ->pluck('sk_user')
            ->all();

        // Directly update DB to avoid possible model-level overwrites
        DB::table('projects')
            ->where('sk_project', $this->sk_project)
            ->update(['pics' => json_encode($ids)]);
    }
}
