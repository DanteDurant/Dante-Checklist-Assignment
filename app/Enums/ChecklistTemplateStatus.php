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

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-50 text-slate-800 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-800',
            self::Published => 'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-900/40',
            self::Archived => 'bg-slate-50 text-slate-600 ring-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:ring-slate-800',
        };
    }
}

