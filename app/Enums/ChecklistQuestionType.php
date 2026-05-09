<?php

namespace App\Enums;

enum ChecklistQuestionType: string
{
    case Boolean = 'boolean';
    case Text = 'text';
    case Textarea = 'textarea';
    case Number = 'number';
    case Date = 'date';
    case DateTime = 'datetime';

    // Choice inputs
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';

    // Backwards-compatible choice types (existing DB values)
    case SingleSelect = 'single_select';
    case MultiSelect = 'multi_select';

    // Specialized text inputs
    case Email = 'email';
    case Phone = 'phone';
    case Url = 'url';

    // Reserved for future (not implemented in Blade flow)
    case Attachment = 'attachment';
}

