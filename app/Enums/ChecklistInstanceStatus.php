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

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-50 text-slate-800 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-800',
            self::InProgress => 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-950/40 dark:text-amber-200 dark:ring-amber-900/40',
            self::Submitted => 'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-900/40',
            self::Approved => 'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-900/40',
            self::Rejected => 'bg-rose-50 text-rose-800 ring-rose-200 dark:bg-rose-950/40 dark:text-rose-200 dark:ring-rose-900/40',
        };
    }
}

