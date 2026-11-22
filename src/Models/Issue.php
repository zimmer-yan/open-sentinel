<?php

namespace OpenSentinel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Issue extends Model
{
    protected $fillable = [
        'project_id',
        'fingerprint_hash',
        'title',
        'type',
        'status',
        'first_seen_at',
        'last_seen_at',
        'event_count',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'immutable_datetime',
            'last_seen_at' => 'immutable_datetime',
            'event_count' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function latestEvent(): HasOne
    {
        return $this->hasOne(Event::class)
            ->latestOfMany('occurred_at');
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('status', 'unresolved');
    }
}
