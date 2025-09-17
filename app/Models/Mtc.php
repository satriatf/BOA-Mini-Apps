<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mtc extends Model
{
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
        'Primajaga'         => 'Primajaga',
    ];

    protected $fillable = [
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
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'attachments' => 'array', 
    ];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_id'); }
    public function resolver(): BelongsTo  { return $this->belongsTo(User::class, 'resolver_id'); }
}
