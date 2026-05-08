<?php

namespace App\Policies;

use App\Models\ChecklistTemplate;
use App\Models\User;

class ChecklistTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'auditor']);
    }

    public function view(User $user, ChecklistTemplate $template): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Auditors can only view published templates.
        return $template->status->value === 'published';
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, ChecklistTemplate $template): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, ChecklistTemplate $template): bool
    {
        return $user->hasRole('admin');
    }
}

