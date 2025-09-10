<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mtc extends Model
{
    public const TYPE_OPTIONS = [
        'PROBLEM'             => 'PROBLEM',
        'REQUEST DATA'             => 'REQUEST DATA',
        'INCIDENT'        => 'INCIDENT',
        'SERVICE REQUEST' => 'SERVICE REQUEST',
        'SUPPORT UAT'     => 'SUPPORT UAT',
    ];

    public const APP_OPTIONS = [
        'Ad1Forflow'      => 'Ad1Forflow',
        'BPKBLib'         => 'BPKBLib',
        'Ihtisar Asuransi'=> 'Ihtisar Asuransi',
        'Ad1Primajaga'    => 'Ad1Primajaga',
    ];

    protected $fillable = [
        'created_by_id','resolver_id','title','deskripsi','type','solusi',
        'application','tanggal','attachments_count',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_id'); }
    public function resolver(): BelongsTo  { return $this->belongsTo(User::class, 'resolver_id'); }
}
