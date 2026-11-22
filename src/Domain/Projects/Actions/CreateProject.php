<?php

namespace OpenSentinel\Domain\Projects\Actions;

use OpenSentinel\Models\Organization;
use OpenSentinel\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateProject
{
    public function execute(
        Organization $organization,
        string $name,
    ): Project {
        return DB::transaction(function () use (
            $organization,
            $name,
        ) {
            $slug = Str::slug($name);

            if ($slug === '') {
                $slug = 'project';
            }

            $base = $slug;
            $suffix = 2;

            while ($organization->projects()
                ->where('slug', $slug)
                ->exists()) {
                $slug = $base.'-'.$suffix++;
            }

            $project = $organization->projects()->create([
                'name' => $name,
                'slug' => $slug,
                'platform' => 'php',
                'status' => 'active',
            ]);

            $project->keys()->create([
                'public_key' => bin2hex(random_bytes(24)),
            ]);

            return $project;
        });
    }
}