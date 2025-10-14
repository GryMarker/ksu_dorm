<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'exists:tenants,id'],
            'type' => ['required', Rule::in(['in', 'out'])],
            'timestamp' => ['nullable', 'date'],
            'mode' => ['nullable', Rule::in(['qr', 'rfid', 'manual'])],
            'device_id' => ['nullable', 'string', 'max:100'],
            'ip' => ['nullable', 'string', 'max:45'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
