<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Holiday extends Model
{
    use SoftDeletes;

    public const UPDATED_AT = null;

    protected $fillable = [
        'date',
        'desc',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (Auth::check() && empty($model->created_by)) {
                // Store employee name instead of ID
                $model->created_by = Auth::user()->employee_name;
            }
        });
    }
}
