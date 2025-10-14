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
            'university_id_no' => ['required', 'string', 'max:50'],
            'program' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:30'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:30'],
            'medical_notes' => ['nullable', 'string'],
            'admission_form' => ['nullable', 'array'],
            'admission_form.*' => ['nullable'],
        ];
    }
}
