<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mtc extends Model
{
    protected $primaryKey = 'sk_mtc';

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
    ];

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
