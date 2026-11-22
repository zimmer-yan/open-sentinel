<?php

namespace OpenSentinel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = ['name', 'slug', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'organization_members',
        )->withPivot('role')->withTimestamps();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
