<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantApplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        // Student-facing application form only
        return $user->isTenant() && ($user->tenant?->type === Tenant::TYPE_STUDENT);
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
            'university_id_no' => [
                'required',
                'string',
                'max:50',
                'regex:/^KSU-[0-9]{4,}$/i',
                Rule::unique('tenants', 'university_id_no')->ignore($this->user()?->tenant?->id),
            ],
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:10'],
            'cellphone' => ['required', 'string', 'max:100'],
            'accept_terms' => ['accepted'],
        ];
    }
}
