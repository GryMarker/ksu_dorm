<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTenant() ?? false;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', Rule::in([Tenant::GENDER_MALE, Tenant::GENDER_FEMALE])],
            'dob' => ['required', 'date', 'before:today'],
            'home_address' => ['required', 'string'],
            'age' => ['required', 'integer', 'min:15', 'max:120'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'father_contact' => ['required', 'string', 'max:100'],
            'mother_name' => ['required', 'string', 'max:255'],
            'mother_contact' => ['required', 'string', 'max:100'],
            'course_year' => ['required', 'string', 'max:255'],
            'cellphone' => ['required', 'string', 'max:100'],
            'accept_terms' => ['accepted'],
        ];
    }
}
