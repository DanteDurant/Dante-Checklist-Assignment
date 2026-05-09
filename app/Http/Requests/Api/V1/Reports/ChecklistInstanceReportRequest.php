<?php

namespace App\Http\Requests\Api\V1\Reports;

use App\Enums\ExportDetailLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChecklistInstanceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'template_id' => ['nullable', 'integer', 'exists:checklist_templates,id'],
            'auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            'q' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'detail' => ['nullable', 'string', Rule::in(array_column(ExportDetailLevel::cases(), 'value'))],
        ];
    }
}
