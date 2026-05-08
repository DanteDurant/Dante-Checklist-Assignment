<?php

namespace App\Http\Requests\Api\V1\Auditor;

use Illuminate\Foundation\Http\FormRequest;

class CompleteChecklistInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\ChecklistInstance $instance */
        $instance = $this->route('instance');

        return $this->user()?->can('complete', $instance) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}

