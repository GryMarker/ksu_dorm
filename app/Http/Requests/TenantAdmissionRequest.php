<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTenant() ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([Tenant::TYPE_STUDENT, Tenant::TYPE_EMPLOYEE])],
            'university_id_no' => [
                'required',
                'string',
                'max:8',
                Rule::when(
                    $this->input('type') === Tenant::TYPE_STUDENT,
                    ['regex:/^\d{2}-\d{5}$/']
                ),
                Rule::unique('tenants', 'university_id_no')->ignore($this->user()?->tenant?->id),
            ],
            'program' => [Rule::requiredIf($this->input('type') === Tenant::TYPE_STUDENT), 'string', 'max:255'],
            'year_level' => [Rule::requiredIf($this->input('type') === Tenant::TYPE_STUDENT), 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:30'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:30'],
            'medical_notes' => ['nullable', 'string'],
            'admission_form' => ['nullable', 'array'],
            'admission_form.*' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'university_id_no.regex' => 'The student ID must use the format 00-00000.',
        ];
    }
}
