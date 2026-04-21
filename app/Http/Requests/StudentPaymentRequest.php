<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class StudentPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user?->isTenant()
            && $user->tenant?->type === Tenant::TYPE_STUDENT
            && $user->tenant?->onboarding_status === Tenant::STATUS_APPROVED;
    }

    public function rules(): array
    {
        return [
            'billing_month' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0'],
            'student_note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
