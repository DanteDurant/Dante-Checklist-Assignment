<?php

namespace App\Enums;

enum ChecklistTemplateStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Active',
            self::Archived => 'Inactive',
        };
    }

    /**
     * @see ChecklistInstanceStatus::badgeClasses()
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-neutral-100 text-neutral-900 ring-neutral-400/70 dark:bg-neutral-800 dark:text-neutral-50 dark:ring-neutral-600',
            self::Published => 'bg-emerald-100 text-emerald-950 ring-emerald-600/70 dark:bg-emerald-950 dark:text-emerald-50 dark:ring-emerald-700',
            self::Archived => 'bg-neutral-100 text-neutral-700 ring-neutral-400/70 dark:bg-neutral-800 dark:text-neutral-300 dark:ring-neutral-600',
        };
    }
}
