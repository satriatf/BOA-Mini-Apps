<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MasterLeaveType extends Model
{
    use SoftDeletes;

    protected $table = 'master_leave_types';

    public const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::user()->employee_name ?? Auth::user()->name;
            }
        });
    }
}
