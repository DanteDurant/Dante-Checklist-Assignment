<?php

namespace App\Policies;

use App\Models\ChecklistInstance;
use App\Models\User;

class ChecklistInstancePolicy
{
    public function view(User $user, ChecklistInstance $instance): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('auditor') && $instance->auditor_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('auditor');
    }

    public function update(User $user, ChecklistInstance $instance): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->hasRole('auditor') && $instance->auditor_id === $user->id;
    }
}

