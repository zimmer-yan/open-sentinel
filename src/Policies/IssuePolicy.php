<?php

namespace OpenSentinel\Policies;

use OpenSentinel\Models\Issue;
use OpenSentinel\Models\Project;
use OpenSentinel\Models\User;

class IssuePolicy
{
    public function view(User $user, Issue $issue): bool
    {
        return $user->can('view', $issue->project);
    }

    public function viewInProject(
        User $user,
        Project $project,
        Issue $issue,
    ): bool {
        return $issue->project_id === $project->id
            && $user->can('view', $project);
    }
}