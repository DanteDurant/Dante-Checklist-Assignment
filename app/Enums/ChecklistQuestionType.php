<?php

namespace App\Enums;

enum ChecklistQuestionType: string
{
    case Boolean = 'boolean';
    case Text = 'text';
    case Number = 'number';
    case Date = 'date';
    case SingleSelect = 'single_select';
    case MultiSelect = 'multi_select';
    case Attachment = 'attachment';
}

