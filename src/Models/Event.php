<?php

namespace OpenSentinel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'project_id',
        'issue_id',
        'event_id',

        'occurred_at',
        'received_at',

        'platform',
        'level',
        'message',
        'environment',
        'release',

        'exception_type',
        'exception_value',
        'stacktrace',

        'trace_id',
        'span_id',

        'runtime_name',
        'runtime_version',
        'server_name',

        'sdk_name',
        'sdk_version',

        'contexts',
        'payload',

        'request',
        'breadcrumbs',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'immutable_datetime',
            'received_at' => 'immutable_datetime',

            'stacktrace' => 'array',
            'contexts' => 'array',
            'payload' => 'array',

            'request' => 'array',
            'breadcrumbs' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
