<?php

namespace OpenSentinel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectKey extends Model
{
    protected $fillable = [
        'project_id',
        'public_key',
        'revoked_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'revoked_at' => 'immutable_datetime',
            'last_used_at' => 'immutable_datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}