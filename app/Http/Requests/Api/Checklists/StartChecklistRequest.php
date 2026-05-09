<?php

namespace App\Http\Requests\Api\Checklists;

use Illuminate\Foundation\Http\FormRequest;

class StartChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('auditor') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}

