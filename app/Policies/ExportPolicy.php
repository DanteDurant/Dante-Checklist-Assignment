<?php

namespace App\Policies;

use App\Models\Export;
use App\Models\User;

class ExportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'auditor']);
    }

    public function view(User $user, Export $export): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $export->user_id === $user->id;
    }

    public function download(User $user, Export $export): bool
    {
        return $this->view($user, $export);
    }
}
