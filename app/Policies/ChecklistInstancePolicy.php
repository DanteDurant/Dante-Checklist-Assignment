<?php

namespace App\Policies;

use App\Enums\ChecklistInstanceStatus;
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

        if (!$user->hasRole('auditor') || $instance->auditor_id !== $user->id) {
            return false;
        }

        // Auditors cannot edit once submitted/finalized.
        return in_array($instance->status, [ChecklistInstanceStatus::Draft, ChecklistInstanceStatus::InProgress], true);
    }

    public function complete(User $user, ChecklistInstance $instance): bool
    {
        return $this->update($user, $instance);
    }
}

