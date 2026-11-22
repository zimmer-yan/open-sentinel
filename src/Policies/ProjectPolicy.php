<?php

namespace OpenSentinel\Policies;

use OpenSentinel\Models\Project;
use OpenSentinel\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $project->organization()
            ->whereHas('members', function ($query) use ($user) {
                $query->whereKey($user->getKey());
            })
            ->exists();
    }
}