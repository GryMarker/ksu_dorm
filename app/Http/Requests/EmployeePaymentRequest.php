<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isEmployee() ?? false;
    }

    public function rules(): array
    {
        return [
            'billing_month' => ['required', 'date_format:Y-m'],
            'salary_deduction' => ['nullable', 'boolean'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'employee_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
