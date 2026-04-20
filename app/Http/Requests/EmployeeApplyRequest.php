<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isEmployee() ?? false;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'sex' => ['required', Rule::in([Tenant::SEX_MALE, Tenant::SEX_FEMALE])],
            'dob' => ['required', 'date', 'before:today'],
            'home_address' => ['required', 'string'],
            'age' => ['required', 'integer', 'min:15', 'max:120'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'cellphone' => ['required', 'string', 'max:100'],
            'salary_deduction' => ['nullable', 'boolean'],
            'family_members' => ['nullable', 'string'],
            'accept_terms' => ['accepted'],
        ];
    }
}
