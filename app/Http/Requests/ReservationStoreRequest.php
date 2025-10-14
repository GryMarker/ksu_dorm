<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTenant() ?? false;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'exists:rooms,id'],
            'bed_id' => ['nullable', 'exists:beds,id'],
            'type' => ['nullable', Rule::in([Reservation::TYPE_INITIAL, Reservation::TYPE_TRANSFER])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
