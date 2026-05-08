<?php

namespace App\Enums;

enum ChecklistTemplateStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}

