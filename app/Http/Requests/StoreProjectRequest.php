<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'case_name' => ['required', 'string', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'work_content' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'period' => ['nullable', 'string', 'max:255'],
            'unit_price' => ['nullable', 'string', 'max:100'],
            'settlement' => ['nullable', 'string', 'max:100'],
            'interview_count' => ['nullable', 'integer', 'min:0', 'max:9'],
            'flow_limit' => ['nullable', 'string', 'max:100'],
            'contract_type' => ['nullable', 'string', 'max:50'],
            'age_limit' => ['nullable', 'string', 'max:50'],
            'foreigner_ok' => ['nullable', 'string', 'max:20'],
            'freelance_ok' => ['nullable', 'string', 'max:20'],
            'memo' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:' . implode(',', Project::STATUSES)],
            'raw_text' => ['nullable', 'string', 'max:20000'],
            'required_skills' => ['nullable', 'array'],
            'required_skills.*' => ['string', 'max:100'],
            'preferred_skills' => ['nullable', 'array'],
            'preferred_skills.*' => ['string', 'max:100'],
        ];
    }
}
