<?php

namespace App\Enums;

enum ChecklistInstanceStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InProgress => 'In Progress',
            self::Submitted => 'Completed',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    /**
     * High-contrast chips for light/dark — neutral base tones + saturated semantic hues.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-neutral-100 text-neutral-900 ring-neutral-400/70 dark:bg-neutral-800 dark:text-neutral-50 dark:ring-neutral-600',
            self::InProgress => 'bg-amber-100 text-amber-950 ring-amber-500/70 dark:bg-amber-950 dark:text-amber-100 dark:ring-amber-700',
            self::Submitted => 'bg-emerald-100 text-emerald-950 ring-emerald-600/70 dark:bg-emerald-950 dark:text-emerald-50 dark:ring-emerald-700',
            self::Approved => 'bg-emerald-100 text-emerald-950 ring-emerald-600/70 dark:bg-emerald-950 dark:text-emerald-50 dark:ring-emerald-700',
            self::Rejected => 'bg-rose-100 text-rose-950 ring-rose-500/70 dark:bg-rose-950 dark:text-rose-50 dark:ring-rose-700',
        };
    }
}
