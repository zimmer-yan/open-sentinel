<?php

namespace OpenSentinel\Policies;

use OpenSentinel\Models\Event;
use OpenSentinel\Models\Project;
use OpenSentinel\Models\User;

class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        return $user->can('view', $event->project);
    }

    public function viewInProject(
        User $user,
        Project $project,
        Event $event,
    ): bool {
        return $event->project_id === $project->id
            && $user->can('view', $project);
    }
}