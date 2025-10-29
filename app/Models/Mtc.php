<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Mtc extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'sk_mtc';

    // Allowed types
    public const TYPE_OPTIONS = [
        'PROBLEM'         => 'PROBLEM',
        'REQUEST DATA'    => 'REQUEST DATA',
        'INCIDENT'        => 'INCIDENT',
        'SERVICE REQUEST' => 'SERVICE REQUEST',
        'SUPPORT UAT'     => 'SUPPORT UAT',
    ];

    // Example applications (pakai punyamu)
    public const APP_OPTIONS = [
        'Ad1Forflow'        => 'Ad1Forflow',
        'BPKBLib'           => 'BPKBLib',
        'Ihtisar Asuransi'  => 'Ihtisar Asuransi',
        'Ad1Primajaga'         => 'Ad1Primajaga',
    ];

    protected $fillable = [
        'sk_mtc',
        'created_by_id',
        'resolver_id',
        'no_tiket',
        'deskripsi',
        'type',
        'solusi',
        'application',
        'tanggal',
        'attachments',
        'attachments_count',
        'is_delete',
        'create_by',
        'create_date',
        'modified_by',
        'modified_date',
        'deleted_at',
    ];
    protected static function booted(): void
    {
        static::creating(function (Mtc $mtc) {
            if (Auth::check() && empty($mtc->create_by)) {
                $mtc->create_by = Auth::user()->employee_name;
            }
        });
    }

    protected $casts = [
        'tanggal'     => 'date',
        'attachments' => 'array',
        'create_date' => 'datetime',
        'modified_date' => 'datetime',
        'is_delete' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id', 'sk_user');
    }
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolver_id', 'sk_user');
    }
}
