<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class OnLeave extends Model
{
    use SoftDeletes;

    protected $table = 'on_leaves';

    // no updated_at
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (Auth::check() && empty($model->user_id)) {
                $model->user_id = Auth::id();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'sk_user');
    }

    public function masterLeaveType()
    {
        // relation by name: leave_type stores the master leave type's name
        return $this->belongsTo(MasterLeaveType::class, 'leave_type', 'name');
    }
}
