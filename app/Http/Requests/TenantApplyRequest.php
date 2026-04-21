<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantApplyRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $studentId = $this->input('university_id_no');

        if (!is_string($studentId)) {
            return;
        }

        $digits = preg_replace('/\D/', '', $studentId);

        if (strlen($digits) > 2 && strlen($digits) <= 7) {
            $this->merge([
                'university_id_no' => substr($digits, 0, 2).'-'.substr($digits, 2),
            ]);
        }
    }

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
            'sex' => ['required', Rule::in([Tenant::SEX_MALE, Tenant::SEX_FEMALE])],
            'dob' => ['required', 'date', 'before:today', 'before_or_equal:'.now()->subYears(15)->toDateString()],
            'home_address' => ['required', 'string'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'father_contact' => ['required', 'string', 'max:100'],
            'mother_name' => ['required', 'string', 'max:255'],
            'mother_contact' => ['required', 'string', 'max:100'],
            'university_id_no' => [
                'required',
                'string',
                'max:8',
                'regex:/^\d{2}-\d{5}$/',
                Rule::unique('tenants', 'university_id_no')->ignore($this->user()?->tenant?->id),
            ],
            'program' => ['required', 'string', 'max:255'],
            'year_level' => ['required', 'string', 'max:10'],
            'cellphone' => ['required', 'string', 'max:100'],
            'accept_terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'university_id_no.regex' => 'The student ID must use the format 00-00000.',
            'dob.before_or_equal' => 'The date of birth must indicate an age of at least 15 years.',
        ];
    }
}
