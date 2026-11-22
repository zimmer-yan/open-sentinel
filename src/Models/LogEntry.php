<?php

namespace OpenSentinel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogEntry extends Model
{
    protected $fillable = [
        'project_id',
        'batch_id',
        'occurred_at',
        'received_at',
        'level',
        'message',
        'logger',
        'environment',
        'release',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'immutable_datetime',
            'received_at' => 'immutable_datetime',
            'attributes' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}